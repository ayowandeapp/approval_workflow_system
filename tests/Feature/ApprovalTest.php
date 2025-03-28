<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Request as RequestModel;

class ApprovalTest extends TestCase
{

    use RefreshDatabase;
    /** @test */
    public function approver_can_approve_request()
    {
        $this->withoutExceptionHandling();

        $approver = $this->authenticate();
        //auth user is at the level
        $newUser = User::factory()->create(['department_id' => $approver->department_id]); //new user in same department
        $requester = User::factory()->create(['department_id' => $approver->department_id]); //requester in same department

        ApprovalFlow::factory()->create(['approver_id' => $newUser->id, 'level' => 2]);

        // Set up approval flow
        ApprovalFlow::factory()->create([
            'approver_id' => $approver->id,
            'level' => 1
        ]);

        $request = RequestModel::factory()->create([
            'requester_id' => $requester->id,
            'status' => RequestStatus::PENDING
        ]);

        // Create pending approval step
        ApprovalStep::factory()->create([
            'request_id' => $request->id,
            'approver_id' => $approver->id,
            'status' => RequestStatus::PENDING
        ]);

        // dd($request->id);

        $response = $this->postJson("/api/requests/{$request->id}/approve", [
            'comment' => 'Looks good!'
        ]);


        // dd($response);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Request approved successfully',
                'request' => [
                    'status' => RequestStatus::PENDING->value, // Still pending if more steps
                    'status_label' => 'pending'
                ]
            ]);

        $this->assertDatabaseHas('approval_steps', [
            'request_id' => $request->id,
            'status' => RequestStatus::APPROVED->value,
            'comment' => 'Looks good!'
        ]);
    }

    /** @test */
    public function approver_can_reject_request()
    {

        $this->withoutExceptionHandling();

        $approver = $this->authenticate();
        //auth user is at the level
        $newUser = User::factory()->create(['department_id' => $approver->department_id]); //new user in same department
        $requester = User::factory()->create(['department_id' => $approver->department_id]); //requester in same department

        ApprovalFlow::factory()->create(['approver_id' => $newUser->id, 'level' => 2]);

        // Set up approval flow
        ApprovalFlow::factory()->create([
            'approver_id' => $approver->id,
            'level' => 1
        ]);

        $request = RequestModel::factory()->create([
            'requester_id' => $requester->id,
            'status' => RequestStatus::PENDING
        ]);

        // Create pending approval step
        ApprovalStep::factory()->create([
            'request_id' => $request->id,
            'approver_id' => $approver->id,
            'status' => RequestStatus::PENDING
        ]);


        $response = $this->postJson("/api/requests/{$request->id}/reject", [
            'comment' => 'Missing information'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Request rejected successfully',
                'request' => [
                    'status' => RequestStatus::REJECTED->value,
                    'status_label' => 'rejected'
                ]
            ]);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => RequestStatus::REJECTED->value
        ]);
    }

    /** @test */
    public function non_approver_cannot_approve()
    {
        $randomUser = $this->authenticate();
        $request = RequestModel::factory()->create();

        $response = $this->postJson("/api/requests/{$request->id}/approve");

        $response->assertStatus(403);
    }
}
