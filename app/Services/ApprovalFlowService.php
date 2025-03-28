<?php
namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class ApprovalFlowService
{
    public function get(Request $request, int $length = 10)
    {
        return ApprovalFlow::with('approver')
            ->when($request->department_id, function ($query) use ($request) {
                $query->whereHas('approver', function ($query) use ($request) {
                    $query->where('department_id', $request->department_id)
                        ->orderBy('department_id');
                });
            })
            ->orderBy('level')
            ->paginate($length);
    }

    public function addApprovalFlow(Request $request): ApprovalFlow
    {
        $validated = $request->validated();
        // Ensure approver isn't already in the flow for this department
        if (ApprovalFlow::where('approver_id', $validated['approver_id'])->exists()) {
            throw new \Exception(
                "This user is already an approver in another workflow.",
                422
            );
        }
        // Ensure level is unique within department
        $approver = User::find($request->approver_id);
        if (!$approver->department_id) {
            throw new \Exception(
                "This user is not associated with a department!",
                422
            );
        }
        $existingLevel = ApprovalFlow::whereHas('approver', function ($query) use ($approver) {
            $query->where('department_id', $approver->department_id);
        })->where('level', $validated['level'])->exists();

        if ($existingLevel) {
            throw new \Exception(
                "This level already has an approver",
                422
            );
        }

        return ApprovalFlow::create($validated);

    }

    public function update(Request $request, ApprovalFlow $approvalFlow)
    {

        $validated = $request->validated();

        if ($request->has('approver_id')) {
            // Ensure new approver isn't already in any workflow
            $exists = ApprovalFlow::where('approver_id', $validated['approver_id'])
                ->where('id', '!=', $approvalFlow->id)
                ->exists();

            if ($exists) {
                throw new \Exception(
                    "This user is already an approver in another workflow.",
                    422
                );
            }

            // Ensure new approver belongs to same department as original
            $newApproverDept = User::find($validated['approver_id'])->department_id;
            $originalDept = $approvalFlow->approver->department_id;

            if ($newApproverDept !== $originalDept) {
                throw new \Exception(
                    "New approver must belong to the same department.",
                    422
                );
            }
        }

        // Ensure level is unique within department

        if ($request->has('level')) {

            $departmentId = $approvalFlow->approver->department_id;
            $exists = ApprovalFlow::whereHas('approver', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
                ->where('level', $validated['level'])
                ->where('id', '!=', $approvalFlow->id)
                ->exists();

            if ($exists) {
                throw new \Exception(
                    "This level already exists in the department.",
                    422
                );
            }
        }

        // Ensure at least one field is being updated
        if (empty($validated)) {
            throw new \Exception(
                "No fields to update provided",
                422
            );
        }

        $approvalFlow->update($validated);

        return $approvalFlow;

    }

    public function destroy(ApprovalFlow $approvalFlow)
    {
        DB::transaction(function () use ($approvalFlow) {
            // Get the department from the approver being removed
            $departmentId = $approvalFlow->approver->department_id;

            $approvalFlow->delete();
            // Get all flows with higher levels
            $higherFlows = ApprovalFlow::whereHas('approver', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
                ->where('level', '>', $approvalFlow->level)
                ->orderBy('level')
                ->get();

            // Decrement levels of higher approvers
            foreach ($higherFlows as $flow) {
                $flow->update(['level' => $flow->level - 1]);
            }

        });

    }
}