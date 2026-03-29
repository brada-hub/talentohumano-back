<?php

namespace Src\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use App\Models\User;

class OnboardingTokenModel extends Model
{
    protected $table = 'onboarding_tokens';

    protected $fillable = [
        'token',
        'id_persona',
        'activo',
        'usado_en',
        'created_by'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'usado_en' => 'datetime'
    ];

    public function persona()
    {
        return $this->belongsTo(PersonaModel::class, 'id_persona');
    }

    public function creator()
    {
        return $this->belongsTo(\Src\Auth\Infrastructure\Persistence\Models\UserModel::class, 'created_by', 'id_user');
    }
}
