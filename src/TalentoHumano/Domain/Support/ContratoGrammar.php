<?php

namespace Src\TalentoHumano\Domain\Support;

final class ContratoGrammar
{
    public static function fromSexo(?string $sexo): array
    {
        $normalized = mb_strtolower(trim((string) $sexo));
        $isFemale = str_contains($normalized, 'femen');

        return [
            'denominacion' => $isFemale ? 'TRABAJADORA' : 'TRABAJADOR',
            'articulo' => $isFemale ? 'la' : 'el',
            'de_articulo' => $isFemale ? 'de la' : 'del',
            'al_articulo' => $isFemale ? 'a la' : 'al',
            'sujeto' => $isFemale ? 'la TRABAJADORA' : 'el TRABAJADOR',
            'obligado' => $isFemale ? 'obligada' : 'obligado',
            'incorporado' => $isFemale ? 'incorporada' : 'incorporado',
            'retirado' => $isFemale ? 'retirada' : 'retirado',
            'titular_cuenta' => $isFemale
                ? 'cuya titular es la TRABAJADORA'
                : 'cuyo titular es el TRABAJADOR',
            'de_trabajador' => $isFemale ? 'de la TRABAJADORA' : 'del TRABAJADOR',
            'al_trabajador' => $isFemale ? 'a la TRABAJADORA' : 'al TRABAJADOR',
            'el_trabajador' => $isFemale ? 'la TRABAJADORA' : 'el TRABAJADOR',
        ];
    }
}
