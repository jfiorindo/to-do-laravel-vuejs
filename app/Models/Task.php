<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
    'title',
    'description',
    'due_date',
    'user_id',
    'is_completed'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
