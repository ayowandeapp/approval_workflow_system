<?php

namespace App\Http\Middleware;

use App\Enums\RequestStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprover
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestModel = $request->route('requestModel');


        // Validate request is pending
        if ($requestModel->status !== RequestStatus::PENDING) {
            abort(403, 'This request is no longer pending approval');
        }

        // Validate user is current approver
        $isApprover = $requestModel->approvalSteps()
            ->where('status', RequestStatus::PENDING)
            ->where('approver_id', $request->user()->id)
            ->exists();

        if (!$isApprover) {
            abort(403, 'You are not the current approver for this request');
        }
        return $next($request);
    }
}
