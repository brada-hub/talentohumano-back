<?php

namespace Src\Beneficios\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ParentescoModel extends Model
{
    protected $table = 'parentesco';
    protected $primaryKey = 'id_parentesco';
    protected $fillable = ['nombre'];
}
