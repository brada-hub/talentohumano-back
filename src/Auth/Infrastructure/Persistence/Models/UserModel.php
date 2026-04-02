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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
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
}
