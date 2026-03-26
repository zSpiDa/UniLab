<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model //Attachment: allegati
{
    use HasFactory; //permette di usare le Factory

    //fillable = Specifica quali colonne del database possono essere assegnate in massa
    protected $fillable = ['attachable_type','attachable_id','path','name','type','uploaded_by']; //path percorso del file

    public function attachable() { return $this->morphTo(); } //relazione polimorfica
    public function uploader()   { return $this->belongsTo(User::class, 'uploaded_by'); } //uno a molti
}
