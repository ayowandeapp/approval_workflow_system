<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\Department;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_submit_request()
    {
        $department = Department::factory()->create();
        $requester = User::factory()->create(['department_id' => $department->id]);
        $approver = User::factory()->create(['department_id' => $department->id]);

        // Set up approval flow
        ApprovalFlow::factory()->create([
            'approver_id' => $approver->id,
            'level' => 1
        ]);

        $response = $this->actingAs($requester)
            ->postJson('/api/requests', [
                'title' => 'New Equipment',
                'description' => 'Need new laptop'
            ]);

        // Assert response
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Request submitted successfully',
                'request' => [
                    'title' => 'New Equipment',
                    'status_label' => 'pending',
                ]
            ]);

        // Assert database
        $this->assertDatabaseHas('requests', [
            'title' => 'New Equipment',
            'requester_id' => $requester->id
        ]);

        $this->assertDatabaseHas('approval_steps', [
            'approver_id' => $approver->id,
            'status' => 0
        ]);


        $request = RequestModel::first();
        $this->assertEquals($department->id, $request->requester->department_id);
    }

    /** @test */
    public function user_can_view_their_requests()
    {
        $user = $this->authenticate();
        $request = RequestModel::factory()->create(['requester_id' => $user->id]);
        RequestModel::factory()->create();

        $response = $this->getJson('/api/requests?requester_id=' . $user->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $request->id]);
    }

    /** @test */
    public function can_view_all_requests()
    {
        $user = $this->authenticate();
        $request = RequestModel::factory()->create(['requester_id' => $user->id]);
        RequestModel::factory()->create();

        $response = $this->getJson('/api/requests');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $request->id]);
    }

    /** @test */
    public function shows_approval_steps()
    {
        $user = $this->authenticate();
        $request = RequestModel::factory()->create(['requester_id' => $user->id]);
        $step = ApprovalStep::factory()->create(['request_id' => $request->id, 'approver_id' => $user->id]);

        $response = $this->getJson('/api/requests/' . $request->id . '/steps');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $step->id]);
    }
}