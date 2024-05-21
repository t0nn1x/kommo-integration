<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'created_by', 'updated_by', 'created_at', 'updated_at',
        'responsible_user_id', 'group_id', 'entity_id', 'entity_type',
        'duration', 'is_completed', 'task_type_id', 'text', 'result',
        'complete_till', 'account_id',
    ];

    protected $casts = [
        'result' => 'array', 
        'is_completed' => 'boolean',
    ];
}
