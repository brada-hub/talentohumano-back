<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegajoDocumentoModel extends Model
{
    protected $table = 'legajo_documentos';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_empleado',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_mime',
        'tamanio',
        'categoria',
        'estado',
        'observaciones'
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadoModel::class, 'id_empleado', 'id_empleado');
    }
}
