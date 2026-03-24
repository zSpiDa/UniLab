<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Group;
use App\Models\User;

class PageController extends Controller
{
    public function home(){
        $group = Group::with('users')->first();
        $allUsers = User::all();
        $projectCount = Project::count();

        return view('home', [
            'projectCount' => $projectCount,
            'group' => $group,
            'allUsers' => $allUsers,
            'message' => $projectCount === 0 ? 'Non ci sono progetti al momento.' : null,
        ]);
    }

    public function about()
    {
        return '<h2>Chi siamo</h2>
            <p>Questo gestionale supporta la gestione di progetti e pubblicazioni del gruppo IVU Lab.</p>';
    }

    public function contact()
    {
        return '<h2>Contatti</h2>
            <p>Email: <a href="mailto:info@uniba.it">info@uniba.it</a></p>';
    }

    public function projects(){
        $projects = ['Mimmo', 'Sangiulio', 'Cabasele'];
        $html = '<h1> Gruppettino carino: </h1><ul>';
        foreach($projects  as $p){
            $html .= '<li>'.$p.'</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function showProject($name){
        return '<h1> Il progetto di: ' . ucfirst($name) .'</h1>';
    }
}
