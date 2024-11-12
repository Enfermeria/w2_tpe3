<?php
require_once "app/models/generos.model.php";
require_once "app/models/libros.model.php";
require_once "app/views/json.view.php";

class GenerosApiController{
    private $modelGenero;
    private $modelLibro;
    private $view;

    public function __construct() {
        $this->modelGenero = new GenerosModel();
        $this->modelLibro = new LibrosModel();
        $this->view  = new JSONView();
    } //__construct



    /**************************************** getAll ***********************************************/
    // /api/generos  (GET)
    function getAll($req, $res){ // Muestro la tabla con las generos según el filtro
        $filtro = new stdClass();    // creo el filtro

        // verifico si hay un filtro además del orden
        if(isset($req->query->filterBy))
            $filtro->cbfiltrar = strtoupper($req->query->filterBy);
        else
            $filtro->cbfiltrar = "";

        // verifico el valor del texto a usar para filtrar
        if(isset($req->query->filtro))
            $filtro->txtfiltro = $req->query->filtro;
        else
            $filtro->txtfiltro = "";

        switch ($filtro->cbfiltrar){
            case '':
                $generos = $this->modelGenero->getAll();
                return $this->view->response($generos); 
                break;
            case 'GENERO':
                $generos = $this->modelGenero->getFiltradoPorGenero($filtro->txtfiltro);
                return $this->view->response($generos);
                break;
            default:
                $this->view->response('filterBy con valor no válido', 400);
                break;
        }
    } // getAll





    /**************************************** get ***********************************************/
    // /api/generos/:idgenero (GET)
    function get($req, $res){ //muestro un libro
        if (isset($req->params->idgenero))
            $idgenero = $req->params->idgenero; // obtengo el id del genero a mostrar
        else
            return $this->view->response("Formato de idgenero no válido", 400);

        $genero = $this->modelGenero->get($idgenero);
        if (! $genero)
            return $this->view->response("No existe el genero con el idgenero=$idgenero", 404);

        return $this->view->response($genero);
    } // get





    /****************************************** create **********************************************/
    // api/generos (POST)
    public function create($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);
        
        // valido los datos
        if (empty($req->body->genero)) 
            return $this->view->response('Falta completar el género', 400);

        // obtengo los datos
        $genero   = $req->body->genero;

        //inserto los datos en la bd.
        $idgenero = $this->modelGenero->insert($genero);

        if (!$idgenero) {
            return $this->view->response("Error al insertar el genero", 500);
        }

        // buena práctica es devolver el recurso insertado
        $genero = $this->modelGenero->get($idgenero);
        return $this->view->response($genero, 201);
    } //create


    
    
    
    /****************************************** update **********************************************/
    // api/generos/:idgenero (PUT)
    function update($req, $res) {
        if(!$res->user)
            return $this->view->response("No autorizado", 401);

        $idgenero = $req->params->idgenero;

        // valido los datos
        if (empty($req->body->genero)) 
            return $this->view->response('Falta completar el género', 400);

        // obtengo los datos
        $nombreGenero = $req->body->genero;

        // verifico que exista ese idgenero en la bd
        $genero = $this->modelGenero->get($idgenero);
        if (!$genero) {
            return $this->view->response("El genero con el idgenero=$idgenero no existe", 404);
        }

        // modifico ese registro en la bd
        $this->modelGenero->update($idgenero, $nombreGenero);
    
        // obtengo el libro modificado y lo devuelvo en la respuesta
        $genero = $this->modelGenero->get($idgenero);
        $this->view->response($genero, 200);
    } //update
    

    


    /**************************************** delete **********************************************/
    // api/generos/:id (DELETE)
    function delete($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);

        $idgenero = $req->params->idgenero;
        // verifico si existe ese id en la bd
        $genero = $this->modelGenero->get($idgenero);// obtengo la tarea por id
        if (!$genero) // si el registro no existe, devuelve un false, sino devuelve el objeto con el genero
            return $this->view->response("No existe el género con el idgenero=$idgenero", 404);
        
        // verifico si no hay libros que tengan ese idGenero (sino no se puede borrar)
        $cantLibros = $this->modelLibro->cantidadConIdGenero($idgenero);
        if ($cantLibros>0)
            return $this->view->response("No se puede borrar el género con el idgenero=$idgenero porque existen libros de ese género", 403);
        
        // borro ese registro en la bd
        $this->modelGenero->delete($idgenero);
        $this->view->response("El género con el idgenero=$idgenero se eliminó con éxito");
    } //delete

} //class GenerosController





