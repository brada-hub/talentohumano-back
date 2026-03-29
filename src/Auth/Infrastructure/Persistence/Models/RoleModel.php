<?php

namespace Src\Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Src\Auth\Infrastructure\Persistence\Models\PermissionModel;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombres',
        'sistema_id',
    ];

    public function permissions()
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }
}
