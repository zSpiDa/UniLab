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
        return response()->json(
            Publication::with('projects','authors')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //implementare
        $publication = Publication::create($request->all());
        return new PublicationResource($publication);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //implementare
        $publication = Publication::with('projects','authors')->findOrFail($id);
        return new PublicationResource($publication);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //implementare
        $publication = Publication::findOrFail($id);
        $publication->update($request->all());
        return new PublicationResource($publication);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //implementare
        $publication = Publication::findOrFail($id);
        $publication->delete();
        return response()->json(null, 204);

    }
}
