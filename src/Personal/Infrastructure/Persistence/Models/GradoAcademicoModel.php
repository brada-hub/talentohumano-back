<?php

namespace Src\Personal\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class GradoAcademicoModel extends Model
{
    protected $table = 'grado_academico';
    protected $primaryKey = 'id_grado_academico';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
