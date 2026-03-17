<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'role' => $this->role,
        'group' => $this->whenLoaded('group', function () {
            return $this->group ? [
                'id' => $this->group->id,
                'name' => $this->group->name,
            ] : null;
        }),
        'projects' => $this->whenLoaded('projects', function () {
            return $this->projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'role' => $project->pivot->role ?? null,
                    'effort' => $project->pivot->effort ?? null,
                ];
            });
        }),
        'tasks' => $this->whenLoaded('tasks', function () {
            return $this->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                ];
            });
        }),
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}

    public function with($request)
    {
        return [
            'meta' => [
                'version' => '1.0',
                'author' => 'Your Name',
            ],
        ];
    }
}