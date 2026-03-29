<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CajaSaludModel extends Model
{
    protected $table = 'caja_salud';
    protected $primaryKey = 'id_caja';
    protected $fillable = ['nombre'];
}
