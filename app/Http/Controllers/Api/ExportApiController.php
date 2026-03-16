<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportApiController extends Controller
{
    //implementare export in csv di tutti i progetti, pubblicazioni e utenti con le relative relazioni
    public function projects()
    {
        //implementare
        $projects = \App\Models\Project::with('users', 'publications')->get();
        $csv = "id,title,status,start_date,end_date,users,publications\n";
        foreach ($projects as $project) {
            $users = $project->users->pluck('name')->implode('|');
            $publications = $project->publications->pluck('title')->implode('|');
            $csv .= "{$project->id},\"{$project->title}\",{$project->status},{$project->start_date},{$project->end_date},\"{$users}\",\"{$publications}\"\n";
        }
        return response($csv)->header('Content-Type', 'text/csv');
    }

    public function publications()
    {
        //implementare senza il pluck, perche' authors non ha un campo name diretto
        $publications = \App\Models\Publication::with('authors', 'projects')->get();
        $csv = "id,title,venue,doi,status,target_deadline,authors,projects\n";
        foreach ($publications as $publication) {
            $authors = $publication->authors->pluck('name')->implode('|');
            $projects = $publication->projects->pluck('title')->implode('|');
            $csv .= "{$publication->id},\"{$publication->title}\",\"{$publication->venue}\",{$publication->doi},{$publication->status},{$publication->target_deadline},\"{$authors}\",\"{$projects}\"\n";
        }
        return response($csv)->header('Content-Type', 'text/csv');

    }

    public function users()
    {
        //implementare
        $users = \App\Models\User::with('group', 'projects', 'tasks')->get();
        $csv = "id,name,email,group,projects,tasks\n";
        foreach ($users as $user) {
            $group = $user->group ? $user->group->name : '';
            $projects = $user->projects->pluck('title')->implode('|');
            $tasks = $user->tasks->pluck('title')->implode('|');
            $csv .= "{$user->id},\"{$user->name}\",{$user->email},\"{$group}\",\"{$projects}\",\"{$tasks}\"\n";
        }
        return response($csv)->header('Content-Type', 'text/csv');
    }

    public function __invoke(Request $request)
    {
        return response()->json(['message' => 'Export API']);
    }
}