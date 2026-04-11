<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CampusModel extends Model
{
    protected $table = 'campus';
    protected $primaryKey = 'id_campus';
    public $incrementing = true;

    protected $fillable = [
        'nombre',
        'sigla',
        'id_sede',
        'direccion',
        'activo',
    ];

    public function sede()
    {
        return $this->belongsTo(SedeModel::class, 'id_sede');
    }
}
