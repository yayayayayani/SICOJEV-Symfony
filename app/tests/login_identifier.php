<?php

// Prueba aislada: solo usa SQLite en memoria, nunca la base de datos del proyecto.
require dirname(__DIR__).'/vendor/autoload.php';

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Kernel;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///:memory:';
putenv('DATABASE_URL=sqlite:///:memory:');
$kernel = new Kernel('test', true);
$kernel->boot();

try {
    $container = $kernel->getContainer()->get('test.service_container');
    $registry = $container->get('doctrine');
    $em = $registry->getManager();
    (new SchemaTool($em))->createSchema([
        $em->getClassMetadata(Rol::class),
        $em->getClassMetadata(Usuario::class),
    ]);
    $rol = (new Rol())->setNombre('Consulta')->setEstado('A')->setFechaCreacion(new DateTimeImmutable());
    $em->persist($rol);
    $crear = function (string $nombre, string $correo) use ($em, $rol): Usuario {
        $usuario = (new Usuario())->setUsername($nombre)->setCorreo($correo)
            ->setNombreCompleto('Prueba')->setEstado('A')->setRol($rol)
            ->setFechaCreacion(new DateTimeImmutable())->setPassword('hash-de-prueba');
        $em->persist($usuario);
        $em->flush();
        return $usuario;
    };
    $usuario = $crear('consulta', 'Persona@ejemplo.com');
    $provider = new EntityUserProvider($registry, Usuario::class);
    foreach (['consulta', 'persona@ejemplo.com', ' PERSONA@EJEMPLO.COM '] as $identificador) {
        if ($provider->loadUserByIdentifier($identificador)->getId() !== $usuario->getId()) {
            throw new RuntimeException('El identificador no devolvió la cuenta esperada.');
        }
    }
    $rechazar = function (string $identificador) use ($provider): void {
        try {
            $provider->loadUserByIdentifier($identificador);
            throw new RuntimeException('Se aceptó un identificador inexistente o ambiguo.');
        } catch (UserNotFoundException) {
        }
    };
    $rechazar('');
    $rechazar('nadie@ejemplo.com');
    $crear('otra', 'persona@ejemplo.com');
    $rechazar('persona@ejemplo.com');
    $crear('consulta@ejemplo.com', 'tercera@ejemplo.com');
    $crear('cuarta', 'consulta@ejemplo.com');
    $rechazar('consulta@ejemplo.com');
    echo "OK: acceso por usuario y correo, mayúsculas, espacios, inexistentes y colisiones.\n";
} finally {
    $kernel->shutdown();
}
