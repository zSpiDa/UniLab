<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
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
        'type' => $this->type,
        'venue' => $this->venue,
        'doi' => $this->doi,
        'status' => $this->status,
        'target_deadline' => $this->target_deadline,
        'author' => $this->author,

        'authors' => $this->whenLoaded('authors', function () {
            return $this->authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'user_id' => $author->user_id,
                    'name' => optional($author->user)->name,
                    'order' => $author->order,
                    'is_corresponding' => $author->is_corresponding,
                ];
            });
        }),

        'projects' => $this->whenLoaded('projects', function () {
            return $this->projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                ];
            });
        }),

        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        ];
    }
}
