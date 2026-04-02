<?php

namespace Src\Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SistemaModel extends Model
{
    protected $table = 'sistemas';
    protected $primaryKey = 'id_sistema';

    protected $fillable = [
        'sistema',
        'url_sistema',
    ];

    public function roles()
    {
        return $this->hasMany(RoleModel::class, 'sistema_id', 'id_sistema');
    }

    public function permissions()
    {
        return $this->hasMany(PermissionModel::class, 'sistema_id', 'id_sistema');
    }
}
