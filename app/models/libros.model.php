<?php

require_once 'app/models/model.php';

class LibrosModel extends Model {

   
    function get($idLibro) {
        $query = $this->db->prepare('SELECT idlibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero WHERE  idLibro = ? AND libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero');
        $query->execute([$idLibro]);   
      
        $libro = $query->fetch(PDO::FETCH_OBJ);
      
        return $libro;
    } //get
    

    function getAll($orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0) { //devuelve todos las Libros con el nombre del autor y su género
        $miOffset = (int) $offset;
        $miLimit  = (int) $limit;

        // OPCION 1: con execute y array de valores
        // NOTA: POR ALGUNA RAZON ESTÁ DANDO PROBLEMAS EL LIMIT Y OFFSET USANDO ?
        /* $sql1 = 'SELECT idlibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero ' 
            . ' WHERE libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero ORDER BY ' . $orden . ' LIMIT ? OFFSET ? ';
        $query = $this->db->prepare($sql1); 
        $query->execute([$miLimit, $miOffset]);
        */

        // OPCION 2: con bindValue
        $sql1 = 'SELECT idlibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero ' 
           . ' WHERE libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero ORDER BY ' . $orden . ' LIMIT ? OFFSET ? ';
        $query = $this->db->prepare($sql1); 

        // Ligar los valores a los placeholders
        $query->bindValue(1, $miLimit, PDO::PARAM_INT);
        $query->bindValue(2, $miOffset, PDO::PARAM_INT);

        // Ejecutar la consulta
        $query->execute();

        
        /* // OPCION 3: Sin ?
        $sql2 = 'SELECT idlibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero ' 
            . ' WHERE libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero ORDER BY ' . $orden . ' LIMIT ' . $miLimit . ' OFFSET ' . $miOffset;
        $query = $this->db->prepare($sql2); 
        $query->execute();
        */

        $Libros = $query->fetchAll(PDO::FETCH_OBJ);  // 3. Obtengo los datos en un arreglo de objetos
     
        return $Libros;
    } //getAll


    function getPorAutor($idAutor, $orden = 'idlibro') {
        $query = $this->db->prepare('SELECT idlibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero WHERE  libro.idautor = ? AND libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero ORDER BY ' . $orden);
        $query->execute([$idAutor]);   
      
        $libros = $query->fetchAll(PDO::FETCH_OBJ);
      
        return $libros;
    } //getPorAutor
    

    function getFiltradoPor($campoYOperador, $txtfiltro, $orden, $limit = PHP_INT_MAX, $offset = 0){
        $miOffset = (int) $offset;
        $miLimit  = (int) $limit;

        /*
        // OPCION 1: con execute y array de valores
        // NOTA: POR ALGUNA RAZON ESTÁ DANDO PROBLEMAS EL LIMIT Y OFFSET USANDO ?
        $query = $this->db->prepare('SELECT idLibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero WHERE libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero AND ' . $campoYOperador . ' ? ORDER BY ' . $orden . ' LIMIT ? OFFSET ?');
        $query->execute([$txtfiltro, $miLimit, $miOffset]);  
        */

        // OPCION 2: con bindValue
        $query = $this->db->prepare('SELECT idLibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero WHERE libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero AND ' . $campoYOperador . ' ? ORDER BY ' . $orden . ' LIMIT ? OFFSET ?');
        $query->bindValue(1, $txtfiltro, PDO::PARAM_STR);
        $query->bindValue(2, $miLimit, PDO::PARAM_INT);
        $query->bindValue(3, $miOffset, PDO::PARAM_INT);
        $query->execute();

        /*
        // OPCION 3: sin usar ? para LIMIT ni OFFSET
        $query = $this->db->prepare('SELECT idLibro, titulo, edicion, libro.idautor, autor.nombre as nombre, libro.idgenero, genero.genero as genero FROM libro, autor, genero WHERE libro.idautor=autor.idautor AND libro.idgenero=genero.idgenero AND ' . $campoYOperador . ' ? ORDER BY ' . $orden . ' LIMIT ' . $miLimit . ' OFFSET ' . $miOffset);
        $query->execute([$txtfiltro]);
        */

        $libros = $query->fetchAll(PDO::FETCH_OBJ);  // 3. Obtengo los datos en un arreglo de objetos
    
        return $libros;
    }

    
    function getFiltradoPorTitulo($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("titulo LIKE ", $txtfiltro . "%", $orden, $limit, $offset);
    } //getFiltradoPorTitulo


    function getFiltradoPorAutor($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("nombre LIKE ", $txtfiltro . "%", $orden, $limit, $offset);
    } //getFiltradoPorAutor


    function getFiltradoPorGenero($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("genero LIKE ", $txtfiltro . "%", $orden, $limit, $offset);
    } //getFiltradoPorGenero

    
    function getFiltradoPorEdicionMayor($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("edicion > ", $txtfiltro, $orden, $limit, $offset);
    } //getFiltradoPorEdicionMayor


    function getFiltradoPorEdicionMenor($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("edicion < ", $txtfiltro, $orden, $limit, $offset);
    } //getFiltradoPorEdicionMenor


    function getFiltradoPorEdicionIgual($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("edicion = ", $txtfiltro, $orden, $limit, $offset);
    } //getFiltradoPorIgual


    function getFiltradoPorEdicionMayorOIgual($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
        return $this->getFiltradoPor("edicion >= ", $txtfiltro, $orden, $limit, $offset);
    } //getFiltradoPorEdicionMayorOIgual


    function getFiltradoPorEdicionMenorOIgual($txtfiltro, $orden = 'idlibro', $limit = PHP_INT_MAX, $offset = 0){
      return $this->getFiltradoPor("edicion <= ", $txtfiltro, $orden, $limit, $offset);
    } //getFiltradoPorEdicionMayorOIgual




    function insert($titulo, $idAutor, $idGenero, $edicion) { // agrega un nuevo registro a la tabla libros
        $query = $this->db->prepare('INSERT INTO libro (titulo, idautor, idgenero, edicion) VALUES (?, ?, ?, ?)');
        $query->execute([$titulo, $idAutor, $idGenero, $edicion]);
      
        $id = $this->db->lastInsertId();
      
        return $id;
    } // insert
    
    
    function delete($idLibro) { // borra el registro de la tabla libros
        $query = $this->db->prepare('DELETE FROM libro WHERE idLibro = ?');
        $query->execute([$idLibro]);
    } //delete
    
    
    function update($idLibro, $titulo, $idAutor, $idGenero, $edicion) { // actualiza la tabla libros con los parámetros dados
        $query = $this->db->prepare('UPDATE libro SET titulo=?, idautor=?, idgenero=?, edicion=? WHERE idLibro = ?');
        $query->execute([$titulo, $idAutor, $idGenero, $edicion, $idLibro]);
        return $idLibro;
    } //update


    function cantidadConIdGenero($idGenero){ // me devuelve la cantidad de libros con ese idGenero
        $query = $this->db->prepare('SELECT COUNT(*) AS cantidad FROM libro WHERE idgenero=?');
        $query->execute([$idGenero]);

        $respuesta = $query->fetch(PDO::FETCH_OBJ);
        return $respuesta->cantidad;
    } //cantidadConIdGenero

    
    function cantidadConIdAutor($idAutor){ // me devuelve la cantidad de libros con ese idAutor
        $query = $this->db->prepare('SELECT COUNT(*) AS cantidad FROM libro WHERE idautor=?');
        $query->execute([$idAutor]);

        $respuesta = $query->fetch(PDO::FETCH_OBJ);
        return $respuesta->cantidad;
    } //cantidadConIdAutor

}