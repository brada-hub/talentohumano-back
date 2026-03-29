<?php

namespace Src\Academico\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionIntelectualModel extends Model
{
    protected $table = 'produccion_intelectual';
    protected $fillable = ['tipo', 'titulo', 'fecha', 'editorial', 'id_ciudad', 'archivo_respaldo', 'id_persona'];
}
