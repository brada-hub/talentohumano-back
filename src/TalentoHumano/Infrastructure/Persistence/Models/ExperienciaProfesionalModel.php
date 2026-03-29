<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class ExperienciaProfesionalModel extends Model {
    protected $table = 'experiencia_profesional';
    protected $fillable = ['cargo', 'empresa', 'fecha_inicio', 'fecha_fin', 'id_depto', 'archivo_respaldo', 'id_persona'];
    public function depto() { return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_depto'); }
}
