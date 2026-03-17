<?php

namespace App\Http\Controllers\Api;

use App\Models\Publication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PublicationResource;

class PublicationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return PublicationResource::collection(Publication::with('projects','authors.user')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //implementare
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'doi' => 'nullable|string|max:255',
            'status' => 'required|in:draft,submitted,accepted,published',
            'target_deadline' => 'nullable|date',
            'author' => 'nullable|string|max:255',
        ]);
        $publication = Publication::create($validated);
        return new PublicationResource($publication->load(['projects', 'authors.user']));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //implementare
        $publication = Publication::with('projects','authors.user')->findOrFail($id);
        return new PublicationResource($publication);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //implementare
        $publication = Publication::findOrFail($id);
        $validated = $request->validate([
            'title' => 'string|max:255',
            'type' => 'string|max:255',
            'venue' => 'nullable|string|max:255',
            'doi' => 'nullable|string|max:255',
            'status' => 'in:draft,submitted,accepted,published',
            'target_deadline' => 'nullable|date',
            'author' => 'nullable|string|max:255',
        ]);
        $publication->update($validated);
        return new PublicationResource($publication->load(['projects', 'authors.user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //implementare
        $publication = Publication::findOrFail($id);
        $publication->delete();
        return response()->noContent();

    }
}
