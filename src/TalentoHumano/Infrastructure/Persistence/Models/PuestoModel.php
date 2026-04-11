<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PuestoModel extends Model
{
    protected $table = 'puestos';
    protected $primaryKey = 'id_puesto';

    protected $fillable = [
        'codigo',
        'nombre_puesto',
        'id_area',
        'id_cargo',
        'id_grupo_personal',
        'id_sede',
        'plantilla_contractual',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(AreaModel::class, 'id_area', 'id_area');
    }

    public function cargo()
    {
        return $this->belongsTo(CargoModel::class, 'id_cargo', 'id_cargo');
    }

    public function grupoPersonal()
    {
        return $this->belongsTo(GrupoPersonalModel::class, 'id_grupo_personal', 'id_grupo_personal');
    }

    public function sede()
    {
        return $this->belongsTo(SedeModel::class, 'id_sede', 'id_sede');
    }

    public function superiores()
    {
        return $this->belongsToMany(
            self::class,
            'puesto_superiores',
            'id_puesto',
            'id_puesto_superior_ref',
            'id_puesto',
            'id_puesto'
        )->withPivot('tipo_relacion')->withTimestamps();
    }
}
