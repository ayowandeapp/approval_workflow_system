<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_approval_hierarchy()
    {
        $this->withoutExceptionHandling();
        $this->authenticate();

        ApprovalFlow::factory()->count(3)->create();

        $response = $this->getJson("/api/approval-flows");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }


    /** @test */
    public function can_add_approver_to_hierarchy()
    {
        $this->withoutExceptionHandling();
        $user = $this->authenticate();
        // $admin = User::factory()->create(['is_admin' => true]);
        // $department = Department::factory()->create();
        $payload = ApprovalFlow::factory()->raw([]);

        $response = $this->postJson('/api/approval-flows', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Approver added to hierarchy successfully',
                'approval_flow' => [
                    'approver_id' => $payload['approver_id'],
                    'level' => $payload['level']
                ]
            ]);
    }

    /** @test */
    public function cannot_add_duplicate_approver_to_same_department()
    {
        // $this->withoutExceptionHandling();
        $user = $this->authenticate();
        // $payload = ApprovalFlow::factory()->raw([
        //     'approval_id' => $user->id,
        //     'level' => 1
        // ]);

        // $approver = User::factory()->create(['department_id' => $user->department_id]);

        // First addition
        $this->postJson('/api/approval-flows', [
            'approver_id' => $user->id,
            'level' => 1
        ]);

        // Try to add same approver again
        $response = $this->postJson('/api/approval-flows', [
            'approver_id' => $user->id,
            'level' => 2
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'This user is already an approver in another workflow.']);
    }

    /** @test */
    public function can_update_approver_level()
    {

        $this->withoutExceptionHandling();
        $user = $this->authenticate();
        $flow = ApprovalFlow::factory()->create([
            'approver_id' => $user->id,
            'level' => 1
        ]);
        // dd($flow->approver->department_id);

        // $approver = User::factory()->create(['department_id' => $user->department_id]);
        // $flow = ApprovalFlow::factory()->create(['level' => 1]);

        $response = $this->patchJson("/api/approval-flows/{$flow->id}", [
            'level' => 2
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Approver level updated successfully',
                'approval_flow' => [
                    'level' => 2
                ]
            ]);
    }

    /** @test */
    public function cannot_set_duplicate_level_in_same_department()
    {
        $this->withoutExceptionHandling();
        $user = $this->authenticate();
        $flow = ApprovalFlow::factory()->create([
            'approver_id' => $user->id,
            'level' => 1
        ]);

        $user2 = User::factory()->create(['department_id' => $user->department_id]);

        $flow2 = ApprovalFlow::factory()->create([
            'approver_id' => $user2->id,
            'level' => 2
        ]);

        // Try to set flow2 to level 1
        $response = $this->patchJson("/api/approval-flows/{$flow2->id}", [
            'level' => 1
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This level already exists in the department.'
            ]);
    }

    /** @test */
    public function can_remove_approver_from_hierarchy()
    {
        $this->withoutExceptionHandling();
        $user = $this->authenticate();
        $flow = ApprovalFlow::factory()->create([
            'approver_id' => $user->id,
            'level' => 1
        ]);

        $user2 = User::factory()->create(['department_id' => $user->department_id]);

        $flow2 = ApprovalFlow::factory()->create([
            'approver_id' => $user2->id,
            'level' => 2
        ]);

        $response = $this->deleteJson("/api/approval-flows/{$flow->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Approver removed from hierarchy successfully'
            ]);

        $this->assertDatabaseMissing('approval_flows', ['id' => $flow->id]);
        $this->assertEquals(1, $flow2->fresh()->level);
    }

}