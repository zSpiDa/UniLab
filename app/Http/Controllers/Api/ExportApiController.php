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
        $query = Project::with('users', 'publications');

        if($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        if($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }    

        $projects = $query->get();

        $rows = $projects->map(function ($project){
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
        $query = Publication::with('authors.user', 'projects');

    // Filtri query string
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('venue')) {
        $query->where('venue', 'like', '%' . $request->venue . '%');
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('year')) {
        $query->where('year', $request->year);
    }

    if ($request->filled('from_deadline')) {
        $query->where('target_deadline', '>=', $request->from_deadline);
    }

    if ($request->filled('to_deadline')) {
        $query->where('target_deadline', '<=', $request->to_deadline);
    }

    $publications = $query->get();

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
        $query = User::with('group', 'projects', 'tasks');

    // Filtri query string
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    if ($request->filled('group_id')) {
        $query->where('group_id', $request->group_id);
    }

    if ($request->filled('search')) {
        $search = '%' . $request->search . '%';
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', $search)
              ->orWhere('email', 'like', $search);
        });
    }

    if ($request->boolean('has_projects')) {
        $query->whereHas('projects');  // Solo utenti con almeno un progetto
    }

    $users = $query->get();

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