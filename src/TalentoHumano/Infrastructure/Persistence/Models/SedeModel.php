<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SedeModel extends Model
{
    protected $table = 'sedes';
    protected $primaryKey = 'id_sede';
    public $incrementing = true;

    protected $fillable = [
        'id_sede', // Allows explicit ID assignment in seeders
        'nombre',
        'sigla',
        'activo',
        'id_ciudad',
        'id_sede_padre',
    ];

    public function ciudad()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\CiudadModel::class, 'id_ciudad');
    }

    public function padre()
    {
        return $this->belongsTo(SedeModel::class, 'id_sede_padre');
    }

    public function hijos()
    {
        return $this->hasMany(SedeModel::class, 'id_sede_padre');
    }
}
