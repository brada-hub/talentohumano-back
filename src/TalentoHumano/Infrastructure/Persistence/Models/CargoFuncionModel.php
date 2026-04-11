<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CargoFuncionModel extends Model
{
    protected $table = 'cargo_funciones';
    protected $primaryKey = 'id_funcion_cargo';

    protected $fillable = [
        'id_cargo',
        'descripcion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function cargo()
    {
        return $this->belongsTo(CargoModel::class, 'id_cargo', 'id_cargo');
    }
}
