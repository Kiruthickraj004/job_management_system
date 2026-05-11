<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable=['user_id','job_id','status','applied_at','withdrawn_at'];

    public function job(){
        return $this->belongsTo(Job::class);
    }
}
