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
        return parent::toArray($request);
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

    //espore id, name, email, created_at, updated_at, projects, role, groups
    public function toArrayWithProjects(Request $request): array
    {        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'projects' => $this->projects,
            'role' => $this->role,
            'groups' => $this->groups,
        ];
    }

}
