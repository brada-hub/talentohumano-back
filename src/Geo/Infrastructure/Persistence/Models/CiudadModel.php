<?php

namespace Src\Geo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CiudadModel extends Model
{
    protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad';

    protected $fillable = [
        'nombre',
        'departamento_id',
        'id_departamento',
    ];

    public function departamento()
    {
        $foreignKey = Schema::hasColumn($this->getTable(), 'departamento_id') ? 'departamento_id' : 'id_departamento';

        return $this->belongsTo(DepartamentoModel::class, $foreignKey, 'id_departamento');
    }
}
