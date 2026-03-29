<?php

namespace Src\Academico\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CapacitacionesModel extends Model
{
    protected $table = 'capacitaciones';
    protected $fillable = ['nombre_curso', 'institucion', 'fecha', 'carga_horaria', 'id_ciudad', 'archivo_respaldo', 'id_persona'];
}
