<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class NivelJerarquicoModel extends Model
{
    protected $table = 'nivel_jerarquico';
    protected $primaryKey = 'id_jerarquico';
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
