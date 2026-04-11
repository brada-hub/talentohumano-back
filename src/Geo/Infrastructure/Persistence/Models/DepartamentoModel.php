<?php

namespace Src\Geo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DepartamentoModel extends Model
{
    protected $table = 'departamentos';
    protected $primaryKey = 'id_departamento';

    protected $fillable = [
        'nombre',
        'codigo_expedido',
        'pais_id',
        'id_pais',
    ];

    public function pais()
    {
        $foreignKey = Schema::hasColumn($this->getTable(), 'pais_id') ? 'pais_id' : 'id_pais';

        return $this->belongsTo(PaisModel::class, $foreignKey, 'id_pais');
    }
}
