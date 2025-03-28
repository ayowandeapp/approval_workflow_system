<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\DB;

class RequestService
{

    public function get(Request $request, int $length = 10)
    {
        $query = RequestModel::with([
            'requester.department',
            'approvalSteps.approver'
        ])
            ->latest();

        // Non-admins only see their own requests
        if ($request->has('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->paginate(15);
    }


    public function createRequest(array $validated): array
    {
        $user = auth()->user();

        // Validate user has a department
        if (!$user->department_id) {
            throw new \Exception("You must be assigned to a department to submit requests", 422);
        }

        return DB::transaction(function () use ($validated, $user) {
            // Create request
            $requestModel = $user->requests()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => RequestStatus::PENDING
            ]);

            // Get approval hierarchy for user's department
            $approvers = ApprovalFlow::with('approver')
                ->whereHas('approver', function ($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                })
                ->orderBy('level')
                ->get();

            // Create first approval step if approvers exist
            if ($approvers->isNotEmpty()) {
                ApprovalStep::create([
                    'request_id' => $requestModel->id,
                    'approver_id' => $approvers->first()->approver_id,
                    'action_date' => now()
                ]);
            }

            return [
                ...$requestModel->toArray(),
                'status_label' => $requestModel->status_label
            ];
        });
    }


    public function processApprovalAction(
        RequestModel $requestModel,
        RequestStatus $action
    ): JsonResponse {

        return DB::transaction(function () use ($requestModel, $action) {
            // Get current pending step
            $currentStep = $requestModel->approvalSteps()
                ->where('status', RequestStatus::PENDING)
                ->firstOrFail();

            // Update current step
            $currentStep->update([
                'status' => $action,
                'comment' => request('comment'),
                'action_date' => today()
            ]);

            // Handle request status transition
            $this->updateRequestStatus($requestModel, $action);

            return response()->json([
                'message' => "Request {$action->label()} successfully",
                'request' => $requestModel->fresh()->load([
                    'approvalSteps.approver',
                    'requester.department'
                ])->append('status_label')
            ]);
        });

    }

    protected function updateRequestStatus(
        RequestModel $requestModel,
        RequestStatus $action
    ): void {
        if ($action === RequestStatus::REJECTED) {
            $requestModel->update(['status' => RequestStatus::REJECTED]);
            return;
        }

        // For approvals, check if there are more steps
        $nextApprover = ApprovalFlow::whereHas('approver', function ($query) use ($requestModel) {
            $query->where('department_id', $requestModel->requester->department_id);
        })
            ->where('level', '>', $requestModel->approvalSteps()->count())
            ->orderBy('level')
            ->first();

        if ($nextApprover) {
            // Create next approval step
            ApprovalStep::create([
                'request_id' => $requestModel->id,
                'approver_id' => $nextApprover->approver_id,
                'status' => RequestStatus::PENDING,
                'action_date' => now()
            ]);
        } else {
            // Final approval
            $requestModel->update(['status' => RequestStatus::APPROVED]);
        }
    }
}