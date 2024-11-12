<?php
require_once "app/models/autores.model.php";
require_once "app/models/libros.model.php";
require_once "app/views/json.view.php";

class AutoresApiController{
    private $modelAutor;
    private $modelLibro;
    private $view;

    public function __construct() {
        $this->modelAutor = new AutoresModel();
        $this->modelLibro = new LibrosModel();
        $this->view  = new JSONView();
    } //__construct





    /**************************************** getAll ***********************************************/
    // /api/autores  (GET)
    function getAll($req, $res){ //muestro los autores según el filtro
        $filtro = new stdClass();    // creo el filtro

        // verifico si hay un filtro
        if(isset($req->query->filterBy))
            $filtro->cbfiltrar = strtoupper($req->query->filterBy);
        else
            $filtro->cbfiltrar = "";

        // verifico el valor del texto a usar para filtrar
        if(isset($req->query->filtro))
            $filtro->txtfiltro = $req->query->filtro;
        else
            $filtro->txtfiltro = "";


        //aplico filtro
        switch ($filtro->cbfiltrar){  // veo que filtro aplico
            case '': // sin filtro
                $autores = $this->modelAutor->getAll();
                return $this->view->response($autores);
                break;
            case 'NOMBRE':
                $autores = $this->modelAutor->getFiltradoPorNombre($filtro->txtfiltro);
                return $this->view->response($autores);
                break;
            case 'BIOGRAFIA':
                $autores = $this->modelAutor->getFiltradoPorBiografia($filtro->txtfiltro);
                return $this->view->response($autores);
                break;
            default:
                $this->view->response('filterBy con valor no válido', 400);
                break;
        }
    } // getAll






    /**************************************** get ***********************************************/
    // /api/autores/:idautor (GET)
    function get($req, $res){ //muestro un autor
        if (isset($req->params->idautor))
            $idautor = $req->params->idautor; // obtengo el id del autor a mostrar
        else
            return $this->view->response("Formato de id de autor no válido", 400);

        $autor = $this->modelAutor->get($idautor);
        if (! $autor)
            return $this->view->response("No existe el libro con el idautor=$idautor", 404);

        return $this->view->response($autor);
    } // get







    /**************************************** delete **********************************************/
    // api/autores/:id (DELETE)
    function delete($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);

        $idautor = $req->params->idautor;
        
        // verifico si existe ese id en la bd
        $autor = $this->modelAutor->get($idautor);// obtengo la tarea por id
        if (!$autor) // si el registro no existe, devuelve un false, sino devuelve el objeto con el autor
            return $this->view->response("No existe el autor con el idautor=$idautor", 404);

        // verifico si no hay libros que tengan ese idautor (sino no se puede borrar)
        $cantLibros = $this->modelLibro->cantidadConIdAutor($idautor);
        if ($cantLibros>0)
            return $this->view->response("No se puede borrar el autor con el idautor=$idautor porque existen libros de ese autor", 403);
            
        // borro ese registro en la bd
        $this->modelAutor->delete($idautor);
        $this->view->response("El autor con el idautor=$idautor se eliminó con éxito");
    } //delete





    /****************************************** create **********************************************/
    // api/libros (POST)
    public function create($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);
        
        // valido los datos
        if (empty($req->body->nombre)) 
            return $this->view->response('Falta completar el nombre del autor', 400);
        if (empty($req->body->biografia))
            return $this->view->response('Falta completar la biografía del autor', 400);
        
        // obtengo los datos
        $nombre     = $req->body->nombre;
        $biografia  = $req->body->biografia;

        /* // Verifico si subió la imagen (en Files)
        if ($_FILES['imagen']['name']) { //subio imagen, si todo ok, inserto datos con imagen
            if ($_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/png") 
                $id = $this->modelAutor->insert($nombre, $biografia, $_FILES['imagen']); //inserto los datos con imagen en la bd
            else 
                return $this->view->response("Formato de imagen no aceptado", 400);
        }
        else  // no subio la imagen, inserto datos sin imagen
            $id = $this->modelAutor->insert($nombre, $biografia); //inserto los datos en la bd
        */

        $id = $this->modelAutor->insert($nombre, $biografia); //inserto los datos en la bd. sin imagen

        if (!$id)
            return $this->view->response("Error al insertar el autor", 500);

        // buena práctica es devolver el recurso insertado
        $autor = $this->modelAutor->get($id);
        return $this->view->response($autor, 201);
    } //create


    

    /****************************************** update **********************************************/
    // api/libros/:id (PUT)
    function update($req, $res) {
        if(!$res->user)
            return $this->view->response("No autorizado", 401);

        $idautor = $req->params->idautor;

        // valido los datos
        if (empty($req->body->nombre)) 
            return $this->view->response('Falta completar el nombre del autor', 400);
        if (empty($req->body->biografia))
            return $this->view->response('Falta completar la biografía del autor', 400);
        
        // obtengo los datos
        $nombre     = $req->body->nombre;
        $biografia  = $req->body->biografia;

        // verifico si existe ese id en la bd
        $autor = $this->modelAutor->get($idautor);
        if (!$autor) // si el registro no existe, devuelve un false, sino devuelve el objeto con el autor
            return $this->view->response("No existe el autor con el idautor=$idautor", 404);

        // modifico ese registro en la bd sin tocar la imagen anterior
        $this->modelAutor->update($idautor, $nombre, $biografia);

        // obtengo el autor modificado y lo devuelvo en la respuesta
        $autor = $this->modelAutor->get($idautor);
        $this->view->response($autor, 200);
    } //update
        


} //class AutoresController





