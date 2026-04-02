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

    public function sistema()
    {
        return $this->belongsTo(SistemaModel::class, 'sistema_id', 'id_sistema');
    }

    public function roles()
    {
        return $this->belongsToMany(
            RoleModel::class,
            'role_has_permissions',
            'permission_id',
            'role_id'
        );
    }
}
