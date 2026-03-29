<?php

namespace Src\Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id_permision';

    protected $fillable = [
        'nombres',
        'sistema_id',
    ];
}
