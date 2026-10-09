<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class IdentidadVisual
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function getLogo(): ?string
    {
        foreach (['png', 'webp', 'jpg', 'jpeg'] as $extension) {
            $ruta = 'images/logo-sjiev.'.$extension;
            if (is_file($this->projectDir.'/public/'.$ruta)) {
                return $ruta;
            }
        }

        return null;
    }
}
