<?php

namespace Src\Recordatorios\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class RecordatorioEnviadoModel extends Model
{
    protected $table = 'recordatorios_enviados';
    protected $primaryKey = 'id_recordatorio';

    protected $fillable = [
        'id_empleado',
        'id_persona',
        'tipo',
        'canal',
        'destinatario',
        'asunto',
        'fecha_evento',
        'automatico',
        'estado',
        'enviado_en',
        'error',
        'payload',
    ];

    protected $casts = [
        'automatico' => 'boolean',
        'fecha_evento' => 'date:Y-m-d',
        'enviado_en' => 'datetime',
        'payload' => 'array',
    ];
}
