<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class FormacionPostgradoModel extends Model {
    protected $table = 'formacion_postgrado';
    protected $fillable = ['tipo', 'nombre_programa', 'institucion', 'fecha_diploma', 'fecha_certificacion', 'id_depto', 'archivo_respaldo', 'id_persona'];
    public function depto() { return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_depto'); }
}
