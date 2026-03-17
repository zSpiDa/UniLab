<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

$query = Project::with(['users','tasks', 'publications', 'milestones', 'tags','attachments','comments.user','group']);

class ProjectApiController extends Controller {
    public function index(Request $r) {
        $query = Project::with('users','tasks','publications');
        if ($r->has('status')) $query->where('status',$r->status);
        if ($r->has('funder')) $query->where('funder','like','%'.$r->funder.'%'); //il where, in questo caso, permette di trovare progetti il cui funder si trovi da qualsiasi parte del testo.
        return ProjectResource::collection($query->get());
    }


    public function show($id) {
        $project = Project::with('users','tasks','publications','tags','attachments','comments.user','group')->find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }
        return new ProjectResource($project);
    }

    public function store(Request $r) {
        $validated = $r->validate([
            'title' => 'required|min:3|max:255',
            'status' => 'required|in:active,ongoing, draft',
            'start_date' => 'nullable|date',
        ]);
        $project = Project::create($validated);
        return new ProjectResource($project);
    }

    public function update(Request $r, Project $project) {
        $validated = $r->validate([
            'title' => 'sometimes|min:3|max:255',
            'status' => 'sometimes|in:active,ongoing, draft',
        ]);
        $project->update($validated);

        return new ProjectResource($project);
    }

    public function destroy(Project $project) {
        $project->delete();
        return response()->json(['message' => 'Project deleted'], 204);
    }
}
