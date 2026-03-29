<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SedeModel extends Model
{
    protected $table = 'sedes';
    protected $primaryKey = 'id_sede';

    protected $fillable = [
        'nombre',
        'id_ciudad',
    ];

    public function ciudad()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\CiudadModel::class, 'id_ciudad');
    }
}
