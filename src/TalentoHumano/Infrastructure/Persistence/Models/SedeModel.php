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
    ];

    public function ciudad()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\CiudadModel::class, 'id_ciudad');
    }
}
