<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request) {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'users' => $this->users->collect(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'effort' => $user->pivot->effort,
                ];
            }),
            'publications' => $this->publications->pluck('title'),
            'milestones' => $this->milestones->pluck('title'),
            'description' => $this->description,
            'file_path' => $this->file_path,
            'code' => $this->code,
            'funder' => $this->funder,
            'end_date' => $this->end_date,
            'group' => $this->group ? $this->group->name : null,
            'tags' => $this->tags->pluck('name'),
            'attachments' => $this->attachments->pluck('file_path'),
            'comments' => $this->comments->pluck('content'),
            'tasks' => $this->tasks->pluck('title'),
            'timestamps' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'links' => [
                'self' => route('api.projects.show', $this->id),
                'publications' => route('api.projects.publications.index', $this->id),
                'milestones' => route('api.projects.milestones.index', $this->id),
                'users' => route('api.projects.users.index', $this->id),
                'tasks' => route('api.projects.tasks.index', $this->id),
            ],
            'permissions' => [
                'can_edit' => auth()->user() && auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user() && auth()->user()->can('delete', $this->resource),
            ],
        ];
    }

}
