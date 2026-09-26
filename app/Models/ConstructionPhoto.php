<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionPhoto extends Model
{
    protected $table = 'construction_photos';
    protected $fillable = ['work_id', 'caption', 'date', 'tone'];

    public function work()
    {
        return $this->belongsTo(ConstructionWork::class, 'work_id', 'id');
    }
}
