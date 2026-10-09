<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UsuarioChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Usuario && ($user->getEstado() !== 'A' || $user->getRol()?->getEstado() !== 'A')) {
            throw new DisabledException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        $this->checkPreAuth($user);
    }
}
