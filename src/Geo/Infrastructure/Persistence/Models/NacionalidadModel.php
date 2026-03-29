<?php

namespace Src\Geo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class NacionalidadModel extends Model
{
    protected $table = 'nacionalidades';
    protected $primaryKey = 'id_nacionalidad';

    protected $fillable = [
        'gentilicio',
        'id_pais',
    ];
}
