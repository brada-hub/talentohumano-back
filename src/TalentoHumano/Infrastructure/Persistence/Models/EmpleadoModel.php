<?php

namespace Src\TalentoHumano\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoModel extends Model
{
    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'id_persona',
        'celular_institucional',
        'correo_institucional',
        'id_caja',
        'nro_matricula_seguro',
        'id_entidad_pensiones',
        'nro_nua_cua',
        'estado_laboral',
    ];

    public function persona()
    {
        return $this->belongsTo(\Src\Personal\Infrastructure\Persistence\Models\PersonaModel::class, 'id_persona');
    }

    public function caja()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\CajaSaludModel::class, 'id_caja');
    }

    public function pensiones()
    {
        return $this->belongsTo(\Src\TalentoHumano\Infrastructure\Persistence\Models\EntidadPensionesModel::class, 'id_entidad_pensiones');
    }

    public function contratos()
    {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel::class, 'id_empleado');
    }

    public function contratoActivo()
    {
        return $this->hasOne(\Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel::class, 'id_empleado')->where('estado_contrato', 'Activo');
    }

    public function legajos()
    {
        return $this->hasMany(\Src\TalentoHumano\Infrastructure\Persistence\Models\LegajoDocumentoModel::class, 'id_empleado');
    }

    public function beneficiarios()
    {
        return $this->hasMany(\Src\Beneficios\Infrastructure\Persistence\Models\BeneficiarioModel::class, 'id_empleado');
    }
}
