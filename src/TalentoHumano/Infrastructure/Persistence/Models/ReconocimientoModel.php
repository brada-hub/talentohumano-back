<?php
namespace Src\TalentoHumano\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

class ReconocimientoModel extends Model {
    protected $table = 'reconocimientos';
    protected $fillable = ['titulo_premio', 'institucion_otorgante', 'fecha', 'lugar', 'archivo_respaldo', 'id_persona'];
}
