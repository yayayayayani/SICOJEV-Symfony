<?php

// Ejecutar desde app: php tests/regression.php. No requiere conexión a la base de datos.
require dirname(__DIR__).'/vendor/autoload.php';

use App\Controller\ActividadController;
use App\Entity\Actividad;
use App\Entity\Rol;
use App\Entity\Usuario;
use App\Security\UsuarioChecker;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Validator\Validation;

function verificar(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

$checker = new UsuarioChecker();
foreach (['Administrador' => 'ROLE_ADMIN', 'Tesorero' => 'ROLE_TESORERO', 'Consulta' => 'ROLE_CONSULTA'] as $nombre => $permiso) {
    $rol = (new Rol())->setNombre($nombre)->setEstado('A');
    $usuario = (new Usuario())->setEstado('A')->setRol($rol);
    $checker->checkPreAuth($usuario);
    $checker->checkPostAuth($usuario);
    verificar($usuario->getRoles() === ['ROLE_USER', $permiso], 'Permisos incorrectos para '.$nombre);
}

foreach ([['I', 'A'], ['A', 'I'], ['A', null]] as [$estadoUsuario, $estadoRol]) {
    $usuario = (new Usuario())->setEstado($estadoUsuario);
    if ($estadoRol !== null) {
        $usuario->setRol((new Rol())->setNombre('Administrador')->setEstado($estadoRol));
    }
    foreach (['checkPreAuth', 'checkPostAuth'] as $metodo) {
        try {
            $checker->$metodo($usuario);
            throw new RuntimeException('Se permitió autenticar una cuenta o rol inactivo.');
        } catch (DisabledException) {
        }
    }
}

// Sin servicios de formulario, CSRF o persistencia: cualquier acceso previo
// a la comprobación de permisos provoca un error y hace fallar la prueba.
$container = new Container();
$container->set('security.authorization_checker', new class implements AuthorizationCheckerInterface {
    public function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return in_array($attribute, ['ROLE_USER', 'ROLE_CONSULTA'], true);
    }
});
$controller = new ActividadController();
$controller->setContainer($container);
$em = (new ReflectionClass(EntityManager::class))->newInstanceWithoutConstructor();
foreach (['new', 'edit', 'delete'] as $accion) {
    $request = Request::create('/actividad', 'POST', ['_token' => 'prueba']);
    $argumentos = $accion === 'new' ? [$request, $em] : [$request, new Actividad(), $em];
    try {
        $controller->$accion(...$argumentos);
        throw new RuntimeException('Consulta pudo ejecutar '.$accion);
    } catch (AccessDeniedException) {
    }
}

$validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
verificar(count($validator->validate(new Actividad())) === 3, 'Los campos obligatorios deben rechazarse.');
$actividad = (new Actividad())->setNombre('Reunión')->setTipo('Juvenil')->setEstado('Programada');
verificar(count($validator->validate($actividad)) === 0, 'Una actividad válida debe aceptarse.');
$actividad->setNombre(str_repeat('a', 151))->setTipo(str_repeat('a', 51))->setEstado(str_repeat('a', 31));
verificar(count($validator->validate($actividad)) === 3, 'Las longitudes deben respetar las columnas.');

echo "OK: roles, cuentas inactivas, permisos previos a escritura y validación de actividades.\n";
