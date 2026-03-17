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
    public function toArray($request){
        return [
        'id' => $this->id,
        'title' => $this->title,
        'code' => $this->code,
        'funder' => $this->funder,
        'status' => $this->status,
        'start_date' => $this->start_date,
        'end_date' => $this->end_date,
        'description' => $this->description,
        'file_path' => $this->file_path,

        'users' => $this->whenLoaded('users', function () {
            return $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role ?? null,
                    'effort' => $user->pivot->effort ?? null,
                ];
            });
        }),

        'publications' => $this->whenLoaded('publications', function () {
            return $this->publications->map(function ($publication) {
                return [
                    'id' => $publication->id,
                    'title' => $publication->title,
                    'status' => $publication->status,
                ];
            });
        }),

        'milestones' => $this->whenLoaded('milestones', function () {
            return $this->milestones->map(function ($milestone) {
                return [
                    'id' => $milestone->id,
                    'title' => $milestone->title,
                    'due_date' => $milestone->due_date,
                    'status' => $milestone->status,
                ];
            });
        }),

        'tasks' => $this->whenLoaded('tasks', function () {
            return $this->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                ];
            });
        }),

        'tags' => $this->whenLoaded('tags', function () {
            return $this->tags->pluck('name');
        }),

        'attachments' => $this->whenLoaded('attachments', function () {
            return $this->attachments->pluck('path');
        }),

        'comments' => $this->whenLoaded('comments', function () {
            return $this->comments->pluck('body');
        }),

        'group' => $this->whenLoaded('group', function () {
            return $this->group ? $this->group->name : null;
        }),

        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        ];
    }  

}
