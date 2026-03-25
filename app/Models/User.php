<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; //generare Token, factory, permette invio notifiche

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'group_id'
    ];

    // Relazione necessaria per la dashboard e per accedere ai progetti dell'utente
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role', 'effort')
            ->withTimestamps();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [ //nascosto quando convertito in json
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function group() { return $this->belongsTo(Group::class, 'group_id'); } //molti a uno

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
