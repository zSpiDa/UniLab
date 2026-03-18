<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = ['title','type','venue','doi','status','target_deadline','year', 'author', 'order'];

    public function projects()   { return $this->belongsToMany(Project::class)->withTimestamps(); } //molti a molti
    public function authors()    { return $this->hasMany(Author::class); } //uno a molti
    public function attachments(){ return $this->morphMany(Attachment::class, 'attachable'); } //polimorfica uno a molti
    public function comments()   { return $this->morphMany(Comment::class, 'commentable'); } //polimorfica uno a molti
    public function tags()       { return $this->morphToMany(Tag::class, 'taggable'); } //polimorfica molti a molti
}
