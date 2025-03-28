<?php

namespace App\Http\Controllers;

use App\Models\ApprovalFlow;
use App\Http\Requests\StoreApprovalFlowRequest;
use App\Http\Requests\UpdateApprovalFlowRequest;
use App\Models\User;
use App\Services\ApprovalFlowService;
use DB;
use Illuminate\Http\Request;

class ApprovalFlowController extends Controller
{

    public function __construct(private ApprovalFlowService $approvalFlowService)
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $flows = $this->approvalFlowService->get($request);
        return response()->json($flows, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApprovalFlowRequest $request)
    {
        try {
            $approvalFlow = $this->approvalFlowService->addApprovalFlow($request);

            return response()->json([
                'message' => 'Approver added to hierarchy successfully',
                'approval_flow' => $approvalFlow->load('approver.department')
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ApprovalFlow $approvalFlow)
    {
        //
    }

    /**
     * Update approver level
     */
    public function update(UpdateApprovalFlowRequest $request, ApprovalFlow $approvalFlow)
    {
        try {
            $approvalFlow = $this->approvalFlowService->update($request, $approvalFlow);

            return response()->json([
                'message' => 'Approver level updated successfully',
                'approval_flow' => $approvalFlow->fresh()->load('approver')
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApprovalFlow $approvalFlow)
    {
        try {
            $this->approvalFlowService->destroy($approvalFlow);

            return response()->json([
                'message' => 'Approver removed from hierarchy successfully',
                'remaining_approvers' => ApprovalFlow::whereHas('approver', function ($query) use ($approvalFlow) {
                    $query->where('department_id', $approvalFlow->approver->department_id);
                })
                    ->orderBy('level')
                    ->get()
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }


    }
}
