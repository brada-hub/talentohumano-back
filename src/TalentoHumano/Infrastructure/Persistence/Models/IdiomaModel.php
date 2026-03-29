<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class IdiomaModel extends Model {
    protected $table = 'idiomas';
    protected $fillable = ['idioma', 'nivel_habla', 'nivel_escritura', 'nivel_lee', 'archivo_respaldo', 'id_persona'];
}
