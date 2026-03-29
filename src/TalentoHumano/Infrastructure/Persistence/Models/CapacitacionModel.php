<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class CapacitacionModel extends Model {
    protected $table = 'capacitaciones';
    protected $fillable = ['nombre_curso', 'institucion', 'fecha', 'carga_horaria', 'id_depto', 'archivo_respaldo', 'id_persona'];
    public function depto() { return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_depto'); }
}
