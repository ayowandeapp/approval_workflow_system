<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\Request as RequestModel;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{

    public function __construct(public RequestService $requestService)
    {

    }
    /**
     * List requests with filters
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->requestService->get($request);

        return response()->json($data);
    }

    /**
     * Submit new request (department comes from requester)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        try {
            $data = $this->requestService->createRequest($validated);

            return response()->json([
                'message' => 'Request submitted successfully',
                'request' => $data
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

    }

    /**
     * Get request details
     */
    public function show(RequestModel $request): JsonResponse
    {
        if (!$request) {
            return response()->json([
                'message' => "Request Not Found"
            ], 400);

        }
        return response()->json([
            'request' => $request->load([
                'requester.department',
                'approvalSteps' => function ($query) {
                    $query->orderBy('created_at');
                },
                'approvalSteps.approver'
            ])
        ]);
    }

    /**
     * Get approval steps for request
     */
    public function steps(RequestModel $request): JsonResponse
    {
        return response()->json(
            $request->approvalSteps()
                ->with('approver.department')
                ->orderBy('created_at')
                ->get()
        );
    }
}