<?php

namespace Src\Auth\Infrastructure\Persistence\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, Notifiable;
    
    public function getMorphClass()
    {
        return 'user';
    }

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'id_persona',
        'username',
        'password',
        'activo',
        'debe_cambiar_password',
        'id_sede_scope',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'debe_cambiar_password' => 'boolean',
        'id_sede_scope' => 'integer',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            RoleModel::class,
            'user_has_roles',
            'user_id',
            'role_id'
        );
    }

    public function permissions()
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'user_has_permissions',
            'user_id',
            'permission_id'
        );
    }

    public function persona()
    {
        return $this->belongsTo(\Src\Personal\Infrastructure\Persistence\Models\PersonaModel::class, 'id_persona', 'id');
    }

    public function sede()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel::class, 'id_sede_scope', 'id_sede');
    }
}
