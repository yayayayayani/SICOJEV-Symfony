<?php

namespace App\Command;

use App\Entity\Rol;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:inicializar-sistema',
    description: 'Crea los roles y el primer administrador de SICOJEV.'
)]
class InicializarSistemaCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('Usuario administrador', 'admin');

        if ($this->em->getRepository(Usuario::class)
            ->findOneBy(['username' => $username])) {
            $io->error('Ese usuario ya existe. No se modificó su cuenta.');
            return Command::FAILURE;
        }

        $nombre = $io->ask('Nombre completo', null, function ($valor) {
            if (!is_string($valor) || trim($valor) === '') {
                throw new \RuntimeException('Escribe un nombre.');
            }
            return trim($valor);
        });

        $correo = $io->ask('Correo', null, function ($valor) {
            if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Escribe un correo válido.');
            }
            return $valor;
        });

        $password = $io->askHidden('Contraseña', function ($valor) {
            if (!is_string($valor) || strlen($valor) < 8) {
                throw new \RuntimeException('Usa al menos 8 caracteres.');
            }
            return $valor;
        });

        $roles = [];

        foreach ([
            'Administrador' => 'Administración del sistema',
            'Tesorero' => 'Gestión de ingresos y egresos',
            'Consulta' => 'Consulta de información y reportes',
        ] as $nombreRol => $descripcion) {
            $rol = $this->em->getRepository(Rol::class)
                ->findOneBy(['nombre' => $nombreRol]);

            if (!$rol) {
                $rol = new Rol();
                $rol->setNombre($nombreRol);
                $rol->setDescripcion($descripcion);
                $rol->setEstado('A');
                $rol->setFechaCreacion(new \DateTimeImmutable());

                $this->em->persist($rol);
            }

            $roles[$nombreRol] = $rol;
        }

        $usuario = new Usuario();
        $usuario->setUsername($username);
        $usuario->setNombreCompleto($nombre);
        $usuario->setCorreo($correo);
        $usuario->setEstado('A');
        $usuario->setFechaCreacion(new \DateTimeImmutable());
        $usuario->setRol($roles['Administrador']);
        $usuario->setRoles(['ROLE_ADMIN']);
        $usuario->setPassword(
            $this->hasher->hashPassword($usuario, $password)
        );

        $this->em->persist($usuario);
        $this->em->flush();

        $io->success('Roles y administrador creados correctamente.');

        return Command::SUCCESS;
    }
}