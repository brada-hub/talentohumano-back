<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoPersonalModel extends Model
{
    protected $table = 'grupos_personal';
    protected $primaryKey = 'id_grupo_personal';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function puestos()
    {
        return $this->hasMany(PuestoModel::class, 'id_grupo_personal', 'id_grupo_personal');
    }
}
