<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class AreaModel extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id_area';

    protected $fillable = [
        'nombre_area',
        'id_area_padre',
        'tipo_area',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(AreaModel::class, 'id_area_padre', 'id_area');
    }

    public function children()
    {
        return $this->hasMany(AreaModel::class, 'id_area_padre', 'id_area');
    }

    public function puestos()
    {
        return $this->hasMany(PuestoModel::class, 'id_area', 'id_area');
    }
}
