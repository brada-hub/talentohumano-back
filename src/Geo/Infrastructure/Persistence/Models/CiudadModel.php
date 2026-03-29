<?php

namespace Src\Geo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CiudadModel extends Model
{
    protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad';

    protected $fillable = [
        'nombre',
        'departamento_id',
    ];

    public function departamento()
    {
        return $this->belongsTo(DepartamentoModel::class, 'departamento_id');
    }
}
