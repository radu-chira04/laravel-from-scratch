<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //protected $guarded = []; # for more freedom or ... like below only those
    protected $fillable = ['title', 'description'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }


}
