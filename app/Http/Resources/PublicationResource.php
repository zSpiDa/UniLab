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
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    //esporre id, title, venue, doi, status, target_deadline, author, authors nested, projects nested
    public function toArrayWithDetails(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'venue' => $this->venue,
            'doi' => $this->doi,
            'status' => $this->status,
            'target_deadline' => $this->target_deadline,
            'author' => $this->author,
            'authors' => $this->authors,
            'projects' => $this->projects,
        ];
    }
}
