<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionScope extends Model
{
    protected $table = 'construction_scopes';
    protected $fillable = ['work_id', 'scope', 'quantity', 'unit_of_measure', 'weight', 'target', 'actual'];

    public function work()
    {
        return $this->belongsTo(ConstructionWork::class, 'work_id', 'id');
    }
}
