<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Publication;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportApiController extends Controller
{
    public function projects(): StreamedResponse
    {
        $projects = Project::with('users', 'publications')->get();

        $rows = $projects->map(function ($project) {
            return [
                $project->id,
                $project->title,
                $project->status,
                $project->start_date,
                $project->end_date,
                $project->users->pluck('name')->implode('|'),
                $project->publications->pluck('title')->implode('|'),
            ];
        });

        return $this->streamCsv(
            'projects.csv',
            ['id', 'title', 'status', 'start_date', 'end_date', 'users', 'publications'],
            $rows
        );
    }

    public function publications(): StreamedResponse
    {
        $publications = Publication::with('authors.user', 'projects')->get();

        $rows = $publications->map(function ($publication) {
            $authors = $publication->authors
                ->map(fn ($author) => optional($author->user)->name)
                ->filter()
                ->implode('|');

            return [
                $publication->id,
                $publication->title,
                $publication->venue,
                $publication->doi,
                $publication->status,
                $publication->target_deadline,
                $authors,
                $publication->projects->pluck('title')->implode('|'),
            ];
        });

        return $this->streamCsv(
            'publications.csv',
            ['id', 'title', 'venue', 'doi', 'status', 'target_deadline', 'authors', 'projects'],
            $rows
        );
    }

    public function users(): StreamedResponse
    {
        $users = User::with('group', 'projects', 'tasks')->get();

        $rows = $users->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                optional($user->group)->name,
                $user->projects->pluck('title')->implode('|'),
                $user->tasks->pluck('title')->implode('|'),
            ];
        });

        return $this->streamCsv(
            'users.csv',
            ['id', 'name', 'email', 'group', 'projects', 'tasks'],
            $rows
        );
    }

    private function streamCsv(string $filename, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8: migliora apertura in Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $header);

            foreach ($rows as $row) {
                fputcsv($out, $row); // escaping robusto automatico
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}