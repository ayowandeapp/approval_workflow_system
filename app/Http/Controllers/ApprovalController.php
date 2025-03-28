<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\ApprovalStep;
use App\Models\Request as RequestModel;
use App\Models\ApprovalFlow;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{

    public function __construct(public RequestService $requestService)
    {

    }
    /**
     * Approve a request step
     */
    public function approve(Request $request, RequestModel $requestModel): JsonResponse
    {
        return $this->requestService->processApprovalAction($requestModel, RequestStatus::APPROVED);
    }

    /**
     * Reject a request step
     */
    public function reject(Request $request, RequestModel $requestModel): JsonResponse
    {
        return $this->requestService->processApprovalAction($requestModel, RequestStatus::REJECTED);
    }


}