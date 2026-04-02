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

    public function sistema()
    {
        return $this->belongsTo(SistemaModel::class, 'sistema_id', 'id_sistema');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }

    public function users()
    {
        return $this->belongsToMany(
            UserModel::class,
            'user_has_roles',
            'role_id',
            'user_id'
        );
    }
}
