<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;
    protected $table = 'job_position';
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class, 'job_position_id');
    }
}
