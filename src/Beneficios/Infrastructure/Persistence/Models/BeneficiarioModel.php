<?php

namespace Src\Beneficios\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiarioModel extends Model
{
    protected $table = 'beneficiarios';
    protected $primaryKey = 'id_beneficiario';

    protected $fillable = [
        'id_empleado',
        'primer_apellido',
        'segundo_apellido',
        'nombres',
        'ci',
        'complemento',
        'id_ci_expedido',
        'fecha_nacimiento',
        'id_parentesco',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function parentesco()
    {
        return $this->belongsTo(ParentescoModel::class, 'id_parentesco');
    }

    public function expedido()
    {
        return $this->belongsTo(\Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel::class, 'id_ci_expedido');
    }
}
