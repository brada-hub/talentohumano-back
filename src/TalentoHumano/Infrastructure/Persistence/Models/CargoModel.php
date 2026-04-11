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
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function nivelJerarquico()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\NivelJerarquicoModel::class, 'id_nivel_jerarquico', 'id_jerarquico');
    }

    public function funcionesBase()
    {
        return $this->hasMany(CargoFuncionModel::class, 'id_cargo', 'id_cargo')->orderBy('orden');
    }

    public function puestos()
    {
        return $this->hasMany(PuestoModel::class, 'id_cargo', 'id_cargo');
    }
}
