<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_list_departments()
    {
        $this->authenticate();

        Department::factory()->count(2)->create();

        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
    /** @test */
    public function can_create_department()
    {
        $this->authenticate();

        $payload = [
            'name' => 'Engineering',
            'description' => 'Software development team'
        ];

        $response = $this->postJson('/api/departments', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Department created successfully',
                'department' => [
                    'name' => 'Engineering',
                ]
            ]);

        $this->assertDatabaseHas('departments', $payload);
    }

    /** @test */
    public function department_creation_requires_name()
    {
        $this->authenticate();

        $response = $this->postJson('/api/departments', [
            'description' => 'Department without name'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function can_view_department()
    {
        $this->authenticate();

        $department = Department::factory()->create();

        $response = $this->getJson("/api/departments/{$department->id}");

        $response->assertStatus(200)
            ->assertJson([
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name
                ]
            ]);
    }

    /** @test */
    public function can_update_department()
    {
        $this->withoutExceptionHandling();
        $this->authenticate();
        $department = Department::factory()->create();

        $payload = [
            'name' => 'Updated Department Name',
            'description' => 'Updated description'
        ];

        $response = $this->patchJson("/api/departments/{$department->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Department updated successfully',
                'department' => [
                    'name' => 'Updated Department Name',
                    'description' => 'Updated description'
                ]
            ]);
    }

    /** @test */
    public function can_deactivate_department()
    {
        $this->withoutExceptionHandling();
        $this->authenticate();
        $department = Department::factory()->create();

        $response = $this->deleteJson("/api/departments/{$department->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Department deactivated successfully']);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'is_active' => false
        ]);
    }

}