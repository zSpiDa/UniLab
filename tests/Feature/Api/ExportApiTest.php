<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_export_requires_authentication(): void
    {
        $this->getJson('/api/v1/export/projects')->assertUnauthorized();
    }

    public function test_projects_export_returns_csv(): void
    {
        $user = User::factory()->create(['role' => 'pi']);
        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'title' => "Titolo, con \"virgolette\"\nnewline",
            'status' => 'active',
        ]);

        $publication = Publication::factory()->create(['title' => 'Pub 1', 'status' => 'pending']);
        $project->publications()->attach($publication->id);
        $project->users()->attach($user->id, ['role' => 'pi', 'effort' => 50]);

        $response = $this->getJson('/api/v1/export/projects');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename=projects.csv');
        $this->assertStringContainsString('id,title,status,start_date,end_date,users,publications', $response->streamedContent());
    }

    public function test_users_export_is_forbidden_for_non_manager_pi(): void
    {
        $user = User::factory()->create(['role' => 'collaborator']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/export/users')->assertForbidden();
    }
}