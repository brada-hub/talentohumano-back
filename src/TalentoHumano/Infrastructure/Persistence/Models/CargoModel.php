<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CargoModel extends Model
{
    protected $table = 'cargos';
    protected $primaryKey = 'id_cargo';

    protected $fillable = [
        'nombre_cargo',
        'descripcion',
        'id_nivel_jerarquico',
    ];
}
