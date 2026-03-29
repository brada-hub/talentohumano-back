<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class FormacionPregradoModel extends Model {
    protected $table = 'formacion_pregrado';
    protected $fillable = ['nivel', 'institucion', 'carrera', 'fecha_diploma', 'fecha_titulo', 'id_depto', 'archivo_diploma', 'archivo_titulo', 'id_persona'];
    public function depto() { return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_depto'); }
}
