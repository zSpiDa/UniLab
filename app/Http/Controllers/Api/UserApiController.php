<?php 

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    //index e show con relazioni group, projects e tasks
    public function index()
    {
        $users = User::with(['group', 'projects', 'tasks'])->get();
        return UserResource::collection($users);
    }

    public function show($id)
    {
        $user = User::with(['group', 'projects', 'tasks'])->findOrFail($id);
        return new UserResource($user);
    }

}