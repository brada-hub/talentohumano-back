<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class TipoContratoModel extends Model
{
    protected $table = 'tipo_contrato'; // Oops, table name in migration was tipo_contrato
    protected $primaryKey = 'id_tipo_contrato';
    protected $fillable = ['nombre'];
}
