<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Assuming no auth middleware for now as per implementation, but acting as user just in case
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Developer',
            'email' => 'dev@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'developer',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'dev@example.com', 'role' => 'developer']);
    }

    public function test_user_can_create_task_and_assign_users()
    {
        $pm = User::factory()->create(['role' => 'project_manager']);
        $dev1 = User::factory()->create(['role' => 'developer']);
        $dev2 = User::factory()->create(['role' => 'developer']);

        $response = $this->actingAs($pm)->post(route('tasks.store'), [
            'title' => 'New Project Task',
            'description' => 'Fix the bug',
            'assignees' => [$dev1->id, $dev2->id],
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['title' => 'New Project Task']);
        
        $task = Task::where('title', 'New Project Task')->first();
        $this->assertCount(2, $task->assignees);
        $this->assertTrue($task->assignees->contains($dev1));
        $this->assertTrue($task->assignees->contains($dev2));
    }

    public function test_user_can_post_update_on_task()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Desc',
            'created_by' => $user->id
        ]);

        $response = $this->actingAs($user)->post(route('tasks.updates.store', $task), [
            'update' => 'I am working on it',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('task_updates', [
            'task_id' => $task->id,
            'update' => 'I am working on it'
        ]);
    }

    public function test_user_can_update_task_status()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Desc',
            'created_by' => $user->id,
            'status' => 'todo'
        ]);

        $response = $this->actingAs($user)->post(route('tasks.updateStatus', $task), [
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress'
        ]);
    }
}
