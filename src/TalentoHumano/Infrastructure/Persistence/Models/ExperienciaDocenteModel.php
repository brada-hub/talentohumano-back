<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class ExperienciaDocenteModel extends Model {
    protected $table = 'experiencia_docente';
    protected $fillable = ['institucion', 'carrera', 'asignaturas', 'gestion_periodo', 'id_depto', 'archivo_respaldo', 'id_persona'];
    public function depto() { return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_depto'); }
}
