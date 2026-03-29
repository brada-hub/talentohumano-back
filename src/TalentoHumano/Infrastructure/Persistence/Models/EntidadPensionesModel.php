<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class EntidadPensionesModel extends Model
{
    protected $table = 'entidad_pensiones';
    protected $primaryKey = 'id_entidad_pensiones';
    protected $fillable = ['nombre'];
}
