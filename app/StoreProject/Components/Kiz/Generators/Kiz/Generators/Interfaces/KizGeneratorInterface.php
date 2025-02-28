<?php

namespace App\StoreProject\Components\Kiz\Generators\Kiz\Generators\Interfaces;

interface KizGeneratorInterface
{
    public function generateKiz(string $data): ?string;

}
