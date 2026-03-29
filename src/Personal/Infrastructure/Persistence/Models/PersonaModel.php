<?php

namespace Src\Personal\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PersonaModel extends Model
{
    use HasUuids;

    protected $table = 'personas';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'primer_apellido',
        'segundo_apellido',
        'nombres',
        'ci',
        'complemento',
        'id_ci_expedido',
        'id_sexo',
        'fecha_nacimiento',
        'celular_personal',
        'correo_personal',
        'estado_civil',
        'id_nacionalidad',
        'direccion_domicilio',
        'id_ciudad',
        'id_pais',
        'foto',
        'activo',
        'estado_onboarding',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'activo' => 'boolean',
    ];

    public function sexo()
    {
        return $this->belongsTo(\Src\Personal\Infrastructure\Persistence\Models\SexoModel::class, 'id_sexo');
    }

    public function nacionalidad()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\NacionalidadModel::class, 'id_nacionalidad');
    }

    public function ciudad()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\CiudadModel::class, 'id_ciudad');
    }

    public function pais()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\PaisModel::class, 'id_pais');
    }

    public function expedido()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_ci_expedido');
    }

    public function documentos()
    {
        return $this->hasMany(\Src\Personal\Infrastructure\Persistence\Models\DocumentoPersonaModel::class, 'id_persona');
    }

    // --- RELACIONES CV NORMALIZADO ---
    public function formacionPregrado() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPregradoModel::class, 'id_persona');
    }
    public function formacionPostgrado() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPostgradoModel::class, 'id_persona');
    }
    public function experienciaDocente() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaDocenteModel::class, 'id_persona');
    }
    public function experienciaProfesional() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaProfesionalModel::class, 'id_persona');
    }
    public function capacitaciones() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\CapacitacionModel::class, 'id_persona');
    }
    public function produccionIntelectual() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\ProduccionIntelectualModel::class, 'id_persona');
    }
    public function reconocimientos() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\ReconocimientoModel::class, 'id_persona');
    }
    public function idiomas() {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\IdiomaModel::class, 'id_persona');
    }
}
