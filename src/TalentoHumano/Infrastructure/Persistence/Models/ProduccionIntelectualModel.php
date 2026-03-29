<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class ProduccionIntelectualModel extends Model {
    protected $table = 'produccion_intelectual';
    protected $fillable = ['tipo', 'titulo', 'fecha', 'editorial', 'id_depto', 'archivo_respaldo', 'id_persona'];
    public function depto() { return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_depto'); }
}
