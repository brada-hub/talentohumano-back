<?php

namespace Src\Auth\Domain\Entities;

final class User
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $personaId,
        private readonly string $username,
        private readonly string $password,
        private readonly bool $activo = true,
        private readonly array $roles = []
    ) {}

    public function id(): ?int { return $this->id; }
    public function personaId(): ?string { return $this->personaId; }
    public function username(): string { return $this->username; }
    public function password(): string { return $this->password; }
    public function isActivo(): bool { return $this->activo; }

    public function permissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            $perms = is_array($role) ? ($role['permissions'] ?? []) : ($role->permissions ?? []);
            foreach ($perms as $p) {
                if (is_array($p)) {
                    $permissions[] = $p['nombres'] ?? $p['name'] ?? 'error';
                } else {
                    $permissions[] = $p->nombres ?? $p->name ?? 'error';
                }
            }
        }
        return array_values(array_unique($permissions));
    }

    public function toArray(): array
    {
        $roleNames = [];
        $permissionsBySystem = [];

        foreach ($this->roles as $r) {
            $isArr = is_array($r);
            $roleName = $isArr ? ($r['nombres'] ?? $r['name'] ?? 'error') : ($r->nombres ?? $r->name ?? 'error');
            $roleNames[] = $roleName;
            
            $sistema = $isArr ? ($r['sistema'] ?? null) : ($r->sistema ?? null);
            $sistemaName = $sistema ? ($isArr ? ($sistema['sistema'] ?? 'Global') : ($sistema->sistema ?? 'Global')) : 'Global';
            $sistemaSlug = strtolower(str_replace(' ', '_', $sistemaName));
            $url = $sistema ? ($isArr ? ($sistema['url_sistema'] ?? null) : ($sistema->url_sistema ?? null)) : null;

            if (!isset($permissionsBySystem[$sistemaSlug])) {
                $permissionsBySystem[$sistemaSlug] = [
                    'sistema' => $sistemaName,
                    'url' => $url,
                    'roles' => [],
                    'permissions' => []
                ];
            }
            
            $permissionsBySystem[$sistemaSlug]['roles'][] = $roleName;

            $perms = $isArr ? ($r['permissions'] ?? []) : ($r->permissions ?? []);
            foreach ($perms as $p) {
                $permName = is_array($p) ? ($p['nombres'] ?? $p['name'] ?? 'error') : ($p->nombres ?? $p->name ?? 'error');
                $permissionsBySystem[$sistemaSlug]['permissions'][] = $permName;
            }
        }
        
        foreach ($permissionsBySystem as $slug => $data) {
            $permissionsBySystem[$slug]['roles'] = array_values(array_unique($data['roles']));
            $permissionsBySystem[$slug]['permissions'] = array_values(array_unique($data['permissions']));
        }

        return [
            'id_user'         => $this->id,
            'id_persona'      => $this->personaId,
            'username'        => $this->username,
            'activo'          => $this->activo,
            'roles'           => array_values(array_unique($roleNames)),
            'permissions'     => $this->permissions(),
            'access_metadata' => $permissionsBySystem
        ];
    }
}
