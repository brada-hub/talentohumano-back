<?php

namespace Src\Personal\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SexoModel extends Model
{
    protected $table = 'sexo';
    protected $primaryKey = 'id_sexo';

    protected $fillable = [
        'sexo',
    ];
}
