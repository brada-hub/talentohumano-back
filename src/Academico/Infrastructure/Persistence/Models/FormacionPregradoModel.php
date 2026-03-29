<?php

namespace Src\Academico\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class FormacionPregradoModel extends Model
{
    protected $table = 'formacion_pregrado';
    protected $fillable = [
        'nivel', 'institucion', 'carrera', 'fecha_diploma', 'fecha_titulo', 
        'id_ciudad', 'archivo_diploma', 'archivo_titulo', 'id_persona'
    ];
}
