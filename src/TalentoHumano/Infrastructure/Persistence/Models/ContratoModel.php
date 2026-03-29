<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoModel extends Model
{
    protected $table = 'contratos';
    protected $primaryKey = 'id_contrato';

    protected $fillable = [
        'id_empleado',
        'id_tipo_contrato',
        'id_area',
        'id_cargo',
        'salario',
        'fecha_inicio',
        'fecha_fin',
        'id_sede',
        'estado_contrato',
    ];

    public function tipo()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\TipoContratoModel::class, 'id_tipo_contrato');
    }

    public function area()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\AreaModel::class, 'id_area');
    }

    public function cargo()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\CargoModel::class, 'id_cargo');
    }

    public function sede()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel::class, 'id_sede');
    }

    protected $casts = [
        'salario'      => 'float',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];
}
