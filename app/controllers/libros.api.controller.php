<?php
require_once "app/models/libros.model.php";
require_once "app/models/autores.model.php";
require_once "app/models/generos.model.php";
require_once "app/views/json.view.php";

class LibrosApiController{
    private $modelLibro;
    private $modelGenero;
    private $modelAutor;
    private $view;

    public function __construct() {
        $this->modelLibro = new LibrosModel();
        $this->modelGenero = new GenerosModel();
        $this->modelAutor = new AutoresModel();
        $this->view  = new JSONView();
    } //__construct




    /**************************************** getAll ***********************************************/
    // /api/libros  (GET)
    function getAll($req, $res){ //muestro las libros según el filtro
        $filtro = new stdClass();    // creo el filtro

        //Verifico si hay algún orden especificado
        if (isset($req->query->sortBy))
            $filtro->cbordenar = strtoupper($req->query->sortBy);
        elseif (isset($req->query->orderBy))
        $filtro->cbordenar = strtoupper($req->query->orderBy);
        else
            $filtro->cbordenar = "";

        // verifico que el orden sea válido
        switch ($filtro->cbordenar){
            case '': // sin orden
            case 'IDLIBRO': 
                $filtro->orden = 'idlibro';
                break;
            case 'TITULO':
                $filtro->orden = "titulo";
                break;
            case 'AUTOR':
                $filtro->orden = 'nombre';
                break;
            case 'GENERO':
                $filtro->orden = 'genero';
                break;
            case 'EDICION':
                    $filtro->orden = 'edicion';
                    break;
            default:
                return $this->view->response('sort con valor no válido', 400);
                break;
        }

        //veo si quiere asc o desc
        if(isset($req->query->order)) {
            if ($filtro->cbordenar!='' && strtoupper($req->query->order) == 'DESC')
                $filtro->cbordenar .= ' DESC';
            else if ($filtro->cbordenar!='' && strtoupper($req->query->order) == 'ASC')
                $filtro->cbordenar .= ' ASC';
            else
                return $this->view->response('order con valor no válido', 400);
        }
    
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

        // verifico si quiere paginado (page=nn & limit=nn). Calculo offset y db_limit
        $db_limit = PHP_INT_MAX; // si no especificó page o limit, traigo cuantos registros puedo de la bd
        if (isset($req->query->limit)){ // si definio page
            $db_limit = $req->query->limit;
            if (!is_numeric($db_limit) || $db_limit <= 0)
                return $this->view->response("limit con valor no válido", 400);
        }

        $offset = 0;             // si no especificó page, empiezo desde el principio
        if (isset($req->query->page)){ // si definio page
            $page = $req->query->page;
            if (!is_numeric($page) || $page <= 0)
                return $this->view->response("page con valor no válido", 400);
            if (!isset($req->query->limit)) // si no especificó un límite, usa el límite por defecto
                $db_limit = DB_LIMIT; // limite de registros a traer de la bd (definido en config.php)
            $offset = ($page-1)*$db_limit;
        } 

        //aplico filtro
        switch ($filtro->cbfiltrar){  // veo que filtro aplico
            case '': // sin filtro
                $libros = $this->modelLibro->getAll($filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'TITULO':
                $libros = $this->modelLibro->getFiltradoPorTitulo($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'AUTOR':
                $libros = $this->modelLibro->getFiltradoPorAutor($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'GENERO':
                $libros = $this->modelLibro->getFiltradoPorGenero($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'EDICIONMAYOR':
                $libros = $this->modelLibro->getFiltradoPorEdicionMayor($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'EDICION':
                $libros = $this->modelLibro->getFiltradoPorEdicionIgual($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'EDICIONMENOR':
                $libros = $this->modelLibro->getFiltradoPorEdicionMenor($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'EDICIONMENOROIGUAL':
                $libros = $this->modelLibro->getFiltradoPorEdicionMenorOIgual($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            case 'EDICIONMAYOROIGUAL':
                $libros = $this->modelLibro->getFiltradoPorEdicionMayorOIgual($filtro->txtfiltro, $filtro->orden, $db_limit, $offset);
                return $this->view->response($libros);
                break;
            default:
                $this->view->response('filterBy con valor no válido', 400);
                break;
        }
    } // getAll




    /**************************************** get ***********************************************/
    // /api/libros/:idlibro (GET)
    function get($req, $res){ //muestro un libro
        if (isset($req->params->idlibro))
            $idlibro = $req->params->idlibro; // obtengo el id del libro a mostrar
        else
            return $this->view->response("Formato de id de libro no válido", 400);

        $libro = $this->modelLibro->get($idlibro);
        if (! $libro)
            return $this->view->response("No existe el libro con el idlibro=$idlibro", 404);

        return $this->view->response($libro);
    } // get




   /**************************************** delete **********************************************/
   // api/libros/:id (DELETE)
    function delete($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);

        $idlibro = $req->params->idlibro;
        // verifico si existe ese id en la bd
        $libro = $this->modelLibro->get($idlibro);// obtengo la tarea por id
        if (!$libro) // si el registro no existe, devuelve un false, sino devuelve el objeto con la libro
            return $this->view->response("No existe el libro con el idlibro=$idlibro", 404);
        
        // borro ese registro en la bd
        $this->modelLibro->delete($idlibro);
        $this->view->response("El libro con el idlibro=$idlibro se eliminó con éxito");
    } //delete




    /****************************************** create **********************************************/
    // api/libros (POST)
    public function create($req, $res) {
        if(!$res->user) 
            return $this->view->response("No autorizado", 401);
        
        // valido los datos
        if (empty($req->body->titulo)) 
            return $this->view->response('Falta completar el título del libro', 400);
        if (empty($req->body->idautor))
            return $this->view->response('Falta completar el idautor del libro', 400);
        if (empty($req->body->idgenero)) 
            return $this->view->response('Falta completar el idgenero del libro', 400);
        if (empty($req->body->edicion)) 
            return $this->view->response('Falta completar la edición del libro', 400);

        // obtengo los datos
        $titulo   = $req->body->titulo;
        $idAutor  = $req->body->idautor;
        $idGenero = $req->body->idgenero;
        $edicion  = $req->body->edicion;

        //verifico si existe ese idgenero en la bd
        $genero = $this->modelGenero->get($idGenero); //obtengo el genero por id
        if (!$genero)  //si el registro no existe, devuelve un false, sino devuelve el objeto con el genero
            return $this->view->response("No existe el género con el idgenero=$idGenero", 404);
    
        //verifico si existe ese idautor en la bd
        $autor = $this->modelAutor->get($idAutor); //obtengo el autor por id
        if (!$autor)  //si el registro no existe, devuelve un false, sino devuelve el objeto con el genero
            return $this->view->response("No existe el autor con el idautor=$idAutor", 404);
    
        //inserto los datos en la bd.
        $idlibro = $this->modelLibro->insert($titulo, $idAutor, $idGenero, $edicion);

        if (!$idlibro) {
            return $this->view->response("Error al insertar el libro", 500);
        }

        // buena práctica es devolver el recurso insertado
        $libro = $this->modelLibro->get($idlibro);
        return $this->view->response($libro, 201);
    } //create


    
    
    /****************************************** update **********************************************/
    // api/libros/:id (PUT)
    function update($req, $res) {
        if(!$res->user)
            return $this->view->response("No autorizado", 401);

        $idlibro = $req->params->idlibro;

        // valido los datos
        if (empty($req->body->titulo)) 
            return $this->view->response('Falta completar el título del libro', 400);
        if (empty($req->body->idautor))
            return $this->view->response('Falta completar el idautor del libro', 400);
        if (empty($req->body->idgenero)) 
            return $this->view->response('Falta completar el idgenero del libro', 400);
        if (empty($req->body->edicion)) 
            return $this->view->response('Falta completar la edición del libro', 400);

        // obtengo los datos
        $titulo   = $req->body->titulo;
        $idAutor  = $req->body->idautor;
        $idGenero = $req->body->idgenero;
        $edicion  = $req->body->edicion;


        // verifico que exista ese idlibro en la bd
        $libro = $this->modelLibro->get($idlibro);
        if (!$libro) {
            return $this->view->response("El libro con el idlibro=$idlibro no existe", 404);
        }

        //verifico si existe ese idgenero en la bd
        $genero = $this->modelGenero->get($idGenero); //obtengo el genero por id
        if (!$genero)  //si el registro no existe, devuelve un false, sino devuelve el objeto con el genero
            return $this->view->response("No existe el género con el idgenero=$idGenero", 404);
    
        //verifico si existe ese idautor en la bd
        $autor = $this->modelAutor->get($idAutor); //obtengo el autor por id
        if (!$autor)  //si el registro no existe, devuelve un false, sino devuelve el objeto con el genero
            return $this->view->response("No existe el autor con el idautor=$idAutor", 404);
    
        // modifico ese registro en la bd
        $idlibro = $this->modelLibro->update($idlibro, $titulo, $idAutor, $idGenero, $edicion);

        // obtengo el libro modificado y lo devuelvo en la respuesta
        $libro = $this->modelLibro->get($idlibro);
        $this->view->response($libro, 200);
    } //update
    

} // class LibrosController




