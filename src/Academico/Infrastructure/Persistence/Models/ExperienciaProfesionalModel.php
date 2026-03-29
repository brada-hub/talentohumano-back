<?php

namespace Src\Academico\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienciaProfesionalModel extends Model
{
    protected $table = 'experiencia_profesional';
    protected $fillable = ['cargo', 'empresa', 'fecha_inicio', 'fecha_fin', 'id_ciudad', 'archivo_respaldo', 'id_persona'];
}
