<?php

namespace Src\Personal\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoPersonaModel extends Model
{
    use HasFactory;

    protected $table = 'documentos_persona';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_persona',
        'tipo',
        'nombre_archivo',
        'ruta_archivo',
        'formato'
    ];

    public function persona()
    {
        return $this->belongsTo(PersonaModel::class, 'id_persona', 'id_persona');
    }
}
