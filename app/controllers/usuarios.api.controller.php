<?php
require_once "app/models/usuarios.model.php";
require_once 'app/views/json.view.php';
require_once 'libs/jwt.php';


class UsuariosApiController{
    private $model;
    private $view;

    public function __construct() {
        $this->model = new UsuariosModel();
        $this->view  = new JSONView();
    } //__construct


    /******************************************************************************************/
    /***                            Vericación Token                                        ***/
    /******************************************************************************************/
    // /api/usuarios/token  (GET)
    public function getToken() {
        // obtengo el nombre y la contraseña desde el header
        $auth_header = $_SERVER['HTTP_AUTHORIZATION']; // "Basic dXN1YXJpbw=="

        $auth_header = explode(' ', $auth_header); // ["Basic", "dXN1YXJpbw=="]
        if(count($auth_header) != 2) {
            return $this->view->response("Error en los encabezados de auth (no son 2)", 401);
        }
        if($auth_header[0] != 'Basic') {
            return $this->view->response("Error en los datos ingresados (espero Basic)", 401);
        }

        $user_pass = base64_decode($auth_header[1]); // "usuario:password"
        $user_pass = explode(':', $user_pass); // ["usuario", "password"]
        
        // Buscamos El usuario en la base
        $user = $this->model->getByNombre($user_pass[0]); 
        
        // Chequeamos la contraseña
        if($user == null || !password_verify($user_pass[1], $user->passwordhash)) {
            return $this->view->response("Usuario o contraseña incorrectos", 400);
        }
        
        // Generamos el token
        $token = createJWT(array(
            'sub' => $user->idusuario,
            'nombreusuario' => $user->nombreusuario,
            'role' => 'admin',
            'iat' => time(),
            'exp' => time() + JWT_EXP, // vencimiento después de JWT_EXP segundos (definido en config.php)
            'Saludo' => 'Hola',
        ));
        return $this->view->response($token);
    }





    /******************************************************************************************/
    /***                               CRUD Usuarios                                        ***/
    /******************************************************************************************/


    /*************************************** getAll *******************************************/
    // /api/usuarios  (GET)
    function getAll($req, $res){ // Muestro la tabla con los usuarios
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);

        $usuarios = $this->model->getAll();

        $usuarios2  = [];
        foreach ($usuarios as $usuario){ // no muestro el passwordHash por seguridad, así que creo un nuevo arreglo sin ese campo
            $usuario2 = new stdClass();
            $usuario2->idusuario = $usuario->idusuario;
            $usuario2->nombreusuario = $usuario->nombreusuario;
            $usuarios2[] = $usuario2; // lo agrego al final del arreglo $usuarios2;
        }

        return $this->view->response($usuarios2); 
    } // getAll




    /**************************************** get ***********************************************/
    // /api/usuarios/:idusuario (GET)
    function get($req, $res){ //muestro un 
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);

        if (isset($req->params->idusuario))
            $idusuario = $req->params->idusuario; // obtengo el id
        else
            return $this->view->response("Formato de id de usuario no válido", 400);

        $usuario = $this->model->get($idusuario);
        if (! $usuario)
            return $this->view->response("No existe el usuario con el idusuario=$idusuario", 404);

        // no muestro el passwordHash por seguridad, asi que copio el resto de campos a usuario2
        $usuario2 = new stdClass();
        $usuario2->idusuario = $usuario->idusuario;
        $usuario2->nombreusuario = $usuario->nombreusuario;
        return $this->view->response($usuario2);
    } // get



   

    /****************************************** create **********************************************/
    // api/usuarios (POST)
    public function create($req, $res) {
        if(!$res->user) // verifico que tenga el token
            return $this->view->response("No autorizado", 401);
        
        // valido los datos
        if (empty($req->body->nombreusuario)) 
            return $this->view->response('Falta completar el nombreusuario', 400);
        if (empty($req->body->password)) 
            return $this->view->response('Falta completar la contraseña', 400);

        // obtengo los datos
        $nombreUsuario = $req->body->nombreusuario;
        $password      = $req->body->password;

        //verifico que ese usuario no esté en la bd
        $usuario = $this->model->getByNombre($nombreUsuario);
        if ($usuario)  //si el registro no existe, devuelve un false, sino devuelve el objeto con el usuario
            return $this->view->response("Ya existe un usuario con ese nombre =$nombreUsuario", 400);

        //inserto los datos en la bd.
        $id = $this->model->insert($nombreUsuario, password_hash($password, PASSWORD_DEFAULT));


        if (!$id) {
            return $this->view->response("Error al insertar el usuario", 500);
        }

        // buena práctica es devolver el recurso insertado
        $usuario = $this->model->get($id);
        $usuario2 = new stdClass(); // no devolvemos el passwordhash, creamos nueva variable usuario2 sin ese dato
        $usuario2->idusuario = $usuario->idusuario;
        $usuario2->nombreusuario = $usuario->nombreusuario;
        return $this->view->response($usuario2, 201);
    } //create


    
    
    
    /****************************************** update **********************************************/
    // api/usuarios/:idgenero (PUT)
    function update($req, $res) {
        if(!$res->user)
            return $this->view->response("No autorizado", 401);

        $idUsuario = $req->params->idusuario;

        // valido los datos
        if (empty($req->body->nombreusuario)) 
            return $this->view->response('Falta completar el nombreusuario', 400);
        if (empty($req->body->password)) 
            return $this->view->response('Falta completar el password', 400);

        // obtengo los datos
        $nombreUsuario = $req->body->nombreusuario;
        $password      = $req->body->password;

        //verifico si existe ese nombreUsuario en la bd
        $usuario = $this->model->getByNombre($nombreUsuario); 
        if (!$usuario)  //si el registro no existe, devuelve un false, sino devuelve el objeto con el usuario
            return $this->view->response("No existe el usuario con el nombreUsuario=$nombreUsuario",404);
    
    
        //modifico ese registro en la bd
        $this->model->update($idUsuario, $nombreUsuario,  password_hash($password, PASSWORD_DEFAULT));
    
        // obtengo el usuario modificado y lo devuelvo en la respuesta
        $usuario = $this->model->get($idUsuario);
        $usuario2 = new stdClass(); // no devolvemos el passwordhash, creamos nueva variable usuario2 sin ese dato
        $usuario2->idusuario = $usuario->idusuario;
        $usuario2->nombreusuario = $usuario->nombreusuario;
        $this->view->response($usuario2, 200);
    } //update
    






    /**************************************** delete **********************************************/
    // api/usuarios/:id (DELETE)
    function delete($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);

        $idUsuario = $req->params->idusuario;
        // verifico si existe ese id en la bd
        $usuario = $this->model->get($idUsuario);
        if (!$usuario) // si el registro no existe, devuelve un false, sino devuelve el objeto con el usuario
            return $this->view->response("No existe el usuario con el idusuario=$idUsuario", 404);
        
        // verifico si no es el último usuario existente de la BD (sino no se puede borrar)
        $cantUsuarios = $this->model->cantidadUsuarios();
        if ($cantUsuarios==1)
            return $this->view->response("No se puede borrar el usuario porque es el último que queda", 403);

        // verifico si no es el usuario logueado (sino no se puede borrar)
        if ($res->user->sub == $idUsuario)
            return $this->view->response("No se puede borrar el usuario activo (logueado)", 403);
        
        // borro ese registro en la bd
        $this->model->delete($idUsuario);
        $this->view->response("El usuario con el idusuario=$idUsuario se eliminó con éxito");
    } //delete
    
    
} //class UsuariosApiController





