<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SedeModel extends Model
{
    protected $table = 'sedes';
    protected $primaryKey = 'id_sede';
    public $incrementing = true;

    protected $fillable = [
        'id_sede', 
        'nombre',
        'sigla',
        'id_departamento',
        'activo',
    ];

    public function departamento()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_departamento');
    }

    public function campus()
    {
        return $this->hasMany(CampusModel::class, 'id_sede');
    }
}
