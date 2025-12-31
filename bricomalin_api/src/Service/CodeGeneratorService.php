<?php

namespace App\Service;

class CodeGeneratorService
{
    /**
     * Génère un code aléatoire de 6 chiffres
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

