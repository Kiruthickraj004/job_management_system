<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable=['user_id','title','description','vacancy','total_vacancy','status'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function application(){
        return $this->hasMany(Application::class);
    }
}
