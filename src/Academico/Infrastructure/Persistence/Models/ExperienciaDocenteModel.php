<?php

namespace Src\Academico\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienciaDocenteModel extends Model
{
    protected $table = 'experiencia_docente';
    protected $fillable = ['institucion', 'carrera', 'asignaturas', 'gestion_periodo', 'id_ciudad', 'archivo_respaldo', 'id_persona'];
}
