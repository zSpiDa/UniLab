<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
     // mostra la lista degli utenti
     $users = User::all();
     return view('users.index', compact('users'));
    }

    public function edit()
    {
        // mostra il form per modificare il profilo dell'utente
        $user = auth()->user();
        return view('users.edit', compact('user'));
    }

    public function update(Request $request)
    {
        //aggiorna il profilo dell'utente
        $user = auth()->user();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:pi,manager,researcher,collaborator'],
        ]);
        $user->update($request->all());
        return redirect()->route('users.edit')->with('status', 'Profilo aggiornato con successo!');
    }

    public function destroy()
    {
        //elimina l'account dell'utente
        $user = auth()->user();
        $user->delete();
        return redirect()->route('home')->with('status', 'Account eliminato con successo!');
    }

    public function exportCsv(User $user)
    {
        $user->load(['group', 'projects', 'tasks']);

        return response()->streamDownload(function() use ($user) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['user_id', 'name', 'email', 'role', 'group', 'projects', 'tasks']);
            fputcsv($out, [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                optional($user->group)->name,
                $user->projects->pluck('title')->implode('|'),
                $user->tasks->pluck('title')->implode('|'),
            ]);
            fclose($out);
        }, 'user_'.$user->id.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=user_'.$user->id.'.csv',
        ]);
    }
}
