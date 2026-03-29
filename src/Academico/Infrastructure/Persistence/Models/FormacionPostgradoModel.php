<?php

namespace Src\Academico\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class FormacionPostgradoModel extends Model
{
    protected $table = 'formacion_postgrado';
    protected $fillable = [
        'tipo', 'nombre_programa', 'institucion', 'fecha_diploma', 
        'fecha_certificacion', 'archivo_respaldo', 'id_ciudad', 'id_persona'
    ];
}
