<?php

namespace Src\Geo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class DepartamentoModel extends Model
{
    protected $table = 'departamentos';
    protected $primaryKey = 'id_departamento';

    protected $fillable = [
        'nombre',
        'codigo_expedido',
        'pais_id',
    ];

    public function pais()
    {
        return $this->belongsTo(PaisModel::class, 'pais_id', 'id_pais');
    }
}
