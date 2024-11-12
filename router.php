<?php
/* 
                                TPE - Parte 3

*/

require_once 'config.php';
require_once 'libs/router.php';
require_once 'app/controllers/libros.api.controller.php';
require_once 'app/controllers/generos.api.controller.php';
require_once 'app/controllers/autores.api.controller.php';
require_once 'app/controllers/usuarios.api.controller.php';
require_once 'app/middlewares/jwt.auth.middleware.php';

$router = new Router();

$router->addMiddleware(new JWTAuthMiddleware()); // para verificación de tokens. Llama al método run() cada vez que invoca un método de la tabla de ruteo

#                 endpoint                   verbo      controller               metodo
$router->addRoute('libros',                  'GET',     'LibrosApiController',   'getAll');
$router->addRoute('libros/:idlibro',         'GET',     'LibrosApiController',   'get'   );
$router->addRoute('libros/:idlibro',         'DELETE',  'LibrosApiController',   'delete');
$router->addRoute('libros',                  'POST',    'LibrosApiController',   'create');
$router->addRoute('libros/:idlibro',         'PUT',     'LibrosApiController',   'update');

$router->addRoute('generos',                 'GET',     'GenerosApiController',   'getAll');
$router->addRoute('generos/:idgenero',       'GET',     'GenerosApiController',   'get'   );
$router->addRoute('generos/:idgenero',       'DELETE',  'GenerosApiController',   'delete');
$router->addRoute('generos',                 'POST',    'GenerosApiController',   'create');
$router->addRoute('generos/:idgenero',       'PUT',     'GenerosApiController',   'update');

$router->addRoute('autores',                 'GET',     'AutoresApiController',   'getAll');
$router->addRoute('autores/:idautor',        'GET',     'AutoresApiController',   'get'   );
$router->addRoute('autores/:idautor',        'DELETE',  'AutoresApiController',   'delete');
$router->addRoute('autores',                 'POST',    'AutoresApiController',   'create');
$router->addRoute('autores/:idautor',        'PUT',     'AutoresApiController',   'update');

$router->addRoute('usuarios/token',          'GET',     'UsuariosApiController',  'getToken'); // este debe estar arriba de usuarios/:idusuario para que no confunda token con :idusuario

$router->addRoute('usuarios',                'GET',     'UsuariosApiController',   'getAll');
$router->addRoute('usuarios/:idusuario',     'GET',     'UsuariosApiController',   'get'   );
$router->addRoute('usuarios/:idusuario',     'DELETE',  'UsuariosApiController',   'delete');
$router->addRoute('usuarios',                'POST',    'UsuariosApiController',   'create');
$router->addRoute('usuarios/:idusuario',     'PUT',     'UsuariosApiController',   'update');


$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);




