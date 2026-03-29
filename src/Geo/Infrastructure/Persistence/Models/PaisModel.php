<?php

namespace Src\Geo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PaisModel extends Model
{
    protected $table = 'paises';
    protected $primaryKey = 'id_pais';

    protected $fillable = [
        'nombre',
        'iso2',
        'iso3',
        'prefijo_telefonico',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
