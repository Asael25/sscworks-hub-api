<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProjectPhoto extends Model
{
    protected $table = 'customer_project_photos';
    protected $fillable = ['project_id', 'caption', 'date'];

    public function project()
    {
        return $this->belongsTo(CustomerProject::class, 'project_id', 'id');
    }
}
