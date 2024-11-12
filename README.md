# w2_tpe3: Biblioteca RESTFul

# Integrantes
  * John David Molina Velarde
  * Mateo Fleites



# Descripción
Tercera entrega del Trabajo Práctico Especial de Web 2 Tudai Grupo 66 Biblioteca.
Es un servicio web de tipo RESTFul de una base de datos con su tabla de libros, autores y los géneros de dichos libros. Hay 2 relaciones 1 a N, tanto entre géneros y los libros y entre los autores y los libros. Además se usa una tabla de usuarios para los accesos (login).

Al visualizar los datos de los libros, se traen también los correspondientes datos del nombre del autor y del género al que el libro pertenece. 

  * Todo el sistema usa el patrón MVC. 
  * Se incluye el SQL para la instalación de la base de datos (si no se desa usar el AutoDeploy)
  * Se incluye un usario "webadmin" con clave "admin"



# Tablas
La tabla libro contiene:
  * idlibro (que es la clave Primaria, es autoincremental)
  * titulo
  * idautor (clave foránea que se vincula con la tabla de autores)
  * idgenero (clave foránea que se vincula con la tabla de géneros)
  * edicion

La tabla autor contiene:
  * idautor (que es la clave Primaria, es autoincremental). A esta clave primaria hace referencia la clave foránea libro.idautor
  * nombre
  * biografia

La tabla genero contiene:
  * idgenero (que es la clave Primaria, es autoincremental). A esta clave primaria hace referencia la clave foránea libro.idgenero
  * genero

La tabla usuarios contiene:
  * idusuario (que es la clave Primaria, autoincrmenteal)
  * nombreusuario (es el nombre del usuario, usado para identificarse)
  * passwordhash (es el password_hash($password, PASSWORD_DEFAULT) que se almacena, no almacenamos el password por motivos de seguridad)



# Diagrama Entidad Relación (con relaciones 1 a N)
![DER_libros](https://github.com/user-attachments/assets/c9963d5c-765b-4b22-adab-2e40e5a0fcf0)



---



#  Endpoints
  **GET** `<<BaseUrl>>/api/usuarios/token`

  **GET** `<<BaseUrl>>/api/libros`  
  **GET** `<<BaseUrl>>/api/libros/:idlibro`  
  **POST** `<<BaseUrl>>/api/libros`  
  **PUT** `<<BaseUrl>>/api/libros/:idlibro`  
  **DELETE** `<<BaseUrl>>/api/libros/:idlibro`

  **GET** `<<BaseUrl>>/api/autores`  
  **GET** `<<BaseUrl>>/api/autores/:idautor`  
  **POST** `<<BaseUrl>>/api/autores`  
  **PUT** `<<BaseUrl>>/api/autores/:idautor`  
  **DELETE** `<<BaseUrl>>/api/autores/:idautor`

  **GET** `<<BaseUrl>>/api/generos`  
  **GET** `<<BaseUrl>>/api/generos/:idgenero`  
  **POST** `<<BaseUrl>>/api/generos`  
  **PUT** `<<BaseUrl>>/api/generos/:idgenero`  
  **DELETE** `<<BaseUrl>>/api/generos/:idgenero`

  **GET** `<<BaseUrl>>/api/usuarios`  
  **GET** `<<BaseUrl>>/api/usuarios/:idusuario`  
  **POST** `<<BaseUrl>>/api/usuarios`  
  **PUT** `<<BaseUrl>>/api/usuarios/:idusuario`  
  **DELETE** `<<BaseUrl>>/api/usuarios/:idusuario`




---





## Autenticación

Para dar de borrar, modificar o dar de alta un nuevo libro/autor/género/usuario, los usuarios deben autenticarse. Para ello se identifican con nombre de usuario y clave (ejemplo: webadmin y admin) usando la **Basic Authentication** haciendo un GET al endpoint 
`/api/usuarios/token`. Ello devolverá un **tokenJWT** codificado, que se copiará y en cada operación se enviará como un **Bearer Token** (precedido de la palabra Bearer en el header de **Authorization: Bearer tokenJWT**). Este token solamente tiene un tiempo de vigencia.


- **GET** `<<BaseUrl>>/api/usuarios/token`  
  Este endpoint permite a los usuarios obtener un token JWT. Para utilizarlo, se deben enviar las credenciales en el encabezado de la solicitud en formato Base64 (usuario:contraseña).

  - **iniciar sesión**:  
    - **Nombre de usuario**: `webadmin`  
    - **Contraseña**: `admin`

  - **Respuesta**:  
    Si las credenciales son válidas, se devuelve un token JWT que puede ser utilizado para autenticar futuras solicitudes a la API.





---





## Libros

- **GET** `<<BaseUrl>>/api/libros`  
  Devuelve los libros disponibles en lal base datos, permitiendo opcionalemente aplicar filtros, ordenamiento y paginado de la lista de resultados.

  - **Descripción**:  
    Esta endpoint permite a los usuarios recuperar una lista de libros disponibles, con opciones para filtrar y ordenar los resultados por diferentes campos, además de paginar la lista de resultados.

  - **Ejemplo de uso**:  
      Para obtener el listado de todos los libros:
      ```http
      GET <<BaseUrl>>/api/libros
      ```

  - **Query Params**:  
    - **Ordenamiento**:  
      - `orderBy` o `sortBy`: Campo por el que se desea ordenar los resultados. Los campos válidos pueden incluir:
        - `idlibro`: Ordena los libros por idlibro.
        - `titulo`: Ordena los libros por título.
        - `autor`: Ordena los libros por el autor.
        - `genero`: Ordena los libros por el género.
        - `edicion`: Ordena los libros por el año de edición.
      
      - `order`: Dirección de ordenamiento para el campo especificado en `orderBy` o `sortBy`. Puede ser:
        - `asc`: Orden ascendente (por defecto).
        - `desc`: Orden descendente.
  
      **Ejemplo de Ordenamiento**:  
      Para obtener todos los libros ordenados por autor en orden descendente:
      ```http
      GET <<BaseUrl>>/api/libros?orderBy=autor&order=desc
      ```
      Para obtener todos los libros ordenados por título en orden por defecto (ascendente):
      ```http
      GET <<BaseUrl>>/api/libros?orderBy=autor
      ```

    - **Filtro**:  
      - `filterBy`: Campo por el que se desea filtrar los resultados. Los campos válidos pueden incluir:
        - `titulo`: Filtra los libros por el título especificado (lista los libros cuyo título comienza por el valor del filtro dado).
        - `autor`: Filtra los libros por el autor especificado (lista los libros cuyo autor comienza por el valor del filtro dado).
        - `genero`: Filtra los libros por el género especificado (lista los libros cuyo género comienza por el valor del filtro dado).
        - `edicion`: Filtra los libros que sean del año de edición especificado.
        - `edicionMayor`: Filtra los libros cuyos año de edición sean mayores al especificado.
        - `edicionMayorOIgual`: Filtra los libros cuyos años de edición sean mayores o iguales al especificado.
        - `edicionMenor`: Filtra los libros cuyos años de edición sean menores al especificado.
        - `edicionMenorOIgual`: Filtra los libros cuyos años de edición sean menores o iguales al especificado.

      - `filtro`: Valor que se utilizará para el filtrado. Debe ser el valor específico que se comparará con el campo filtrado.

      **Ejemplo de Filtrado**:  
      Para obtener todos los libros cuyo título empieza por "La":
      ```http
      GET <<BaseUrl>>/api/libros?filterBy=titulo&filtro=La
      ```
      Para obtener todos los libros cuyo autor empieza por "M":
      ```http
      GET <<BaseUrl>>/api/libros?filterBy=autor&filtro=M
      ```

    - **Paginación**:  
      La **paginación** permite dividir los resultados en páginas más pequeñas, mejorando la experiencia del usuario y optimizando el rendimiento de la aplicación.

      - `page`: Número de la página solicitada. Si no se especifica, se muestran todos los libros.
      - `limit`: Número de libros por página. Si no se especifica, se aplica el valor por defecto de 5 como límite.

      **Ejemplo de paginado**:  
      Devolver página 3 con 5 resultados por página:
      ```http
      GET <<BaseUrl>>/api/libros?page=3
      ```
      Devolver página 4 con 10 resultados por página:
      ```http
      GET <<BaseUrl>>/api/libros?page=3&limit=10
      ```
      
---

- **GET** `<<BaseUrl>>/api/libros/:idlibro`  
  Devuelve el libro correspondiente al `idlibro` solicitado.

---

- **POST** `<<BaseUrl>>/api/libros`  
  Authorization: Bearer tokenJWT  
  Crea un nuevo libro con la información en formato JSON que se pone en el body. Luego de insertar se devuelve el idlibro insertado. Debe notarse que para poder crear un nuevo libro e insertarlo en la base de datos, primero debe estar identificado a través de un token de autorización.

  - **Campos requeridos**:  
    - `titulo`: título del libro 
    - `idautor`: identificación del autor del libro. Debe existir en la tabla de autores, sino no permite el alta del libro.
    - `idgenero`: identificación del género del libro. Debe existir en la tabla de géneros, sino no permite el alta del libro.
    - `edicion`: Año de la edición de libro

    Ejemplo:
    ```
    {  
        "titulo": "La Regenta",  
        "idautor": 3,  
        "idgenero": 7,  
        "edicion": 1994  
    }
 	```

  > **Nota**: El campo `idlibro` se genera automáticamente y no debe incluirse en el JSON.

---

- **PUT** `<<BaseUrl>>/api/libros/:idlibro`  
  Authorization: Bearer tokenJWT  
  Modifica los datos del libro correspondiente al `idlibro`. La información a modificar se pone en formato JSON en el body (al igual que el POST). Los campos son los mismos que en el post. Debe notarse que para poder modificar un libro, primero debe estar identificado a través de un token de autorización.

---

- **DELETE** `<<BaseUrl>>/api/libros/:idlibro`  
  Authorization: Bearer tokenJWT  
  Elimina el libro correspondiente al `idlibro`. Debe notarse que para poder eliminar un libro, primero debe estar identificado a través de un token de autorización





---





## Autores

- **GET** `<<BaseUrl>>/api/autores`  
  Devuelve los autores disponibles en la base datos.

---

- **GET** `<<BaseUrl>>/api/autores/:idautor`  
  Devuelve el autor correspondiente al `idautor` solicitado.

---

- **POST** `<<BaseUrl>>/api/autores`  
  Authorization: Bearer tokenJWT  
  Crea un nuevo autor con la información en formato JSON que se pone en el body. Luego de insertar se devuelve el idautor insertado. Debe notarse que para poder crear un nuevo autor e insertarlo en la base de datos, primero debe estar identificado a través de un token de autorización.

  Ej:
  ```
     {  
        "nombre": "Alcott, Louise M.",  
        "biografia": "la alcott famosa"  
     }  
  ```
    
---

- **PUT** `<<BaseUrl>>/api/autores/:idautor`  
  Authorization: Bearer tokenJWT  
  Modifica los datos del libro correspondiente al `idlibro`. La información a modificar se pone en formato JSON en el body (al igual que el POST). Los campos son los mismos que en el post. Debe notarse que para poder modificar un autor, primero debe estar identificado a través de un token de autorización.

---

- **DELETE** `<<BaseUrl>>/api/autores/:idautor`  
  Authorization: Bearer tokenJWT  
  Elimina el autor correspondiente al `idautor`. Debe notarse que para poder eliminar un autor, primero debe estar identificado a través de un token de autorización. También observe que si existen libros con ese autor, no se podrá borrar el autor hasta tanto no borre dichos libros (o modifique su autor).

 



---




## Géneros

- **GET** `<<BaseUrl>>/api/generos`  
  Devuelve los autores disponibles en la base datos.

---

- **GET** `<<BaseUrl>>/api/generos/:idgenero`  
  Devuelve el autor correspondiente al `idgenero` solicitado.

---

- **POST** `<<BaseUrl>>/api/generos`  
  Authorization: Bearer tokenJWT  
  Crea un nuevo género con la información en formato JSON que se pone en el body. Luego de insertar se devuelve el idgenero insertado. Debe notarse que para poder crear un nuevo género e insertarlo en la base de datos, primero debe estar identificado a través de un token de autorización.

  Ej:
  ```
     {  
        "genero": "Ciencia ficción"  
     }  
  ```
---

- **PUT** `<<BaseUrl>>/api/generos/:idgenero`  
  Authorization: Bearer tokenJWT  
  Modifica los datos del género correspondiente al `idgenero`. La información a modificar se pone en formato JSON en el body (al igual que el POST). Los campos son los mismos que en el post. Debe notarse que para poder modificar un género, primero debe estar identificado a través de un token de autorización.

  
---

- **DELETE** `<<BaseUrl>>/api/generos/:idgenero`  
  Authorization: Bearer tokenJWT  
  Elimina el género correspondiente al `idgenero`. Debe notarse que para poder eliminar un género, primero debe estar identificado a través de un token de autorización. También observe que si existen libros con ese género, no se podrá borrar el género hasta tanto no borre dichos libros (o modifique su género).




---




## Usuarios

- **GET** `<<BaseUrl>>/api/usuarios`  
  Authorization: Bearer tokenJWT  
  Devuelve los usuarios disponibles en la base datos. Por un tema de seguridad para poder listar los usuarios debe estar identificado a través de un tokenJWT de autorización. También observe que por seguridad no se muestran las claves ni los hash correspondientes.

---

- **GET** `<<BaseUrl>>/api/autores/:idusuario`  
  Authorization: Bearer tokenJWT  
  Devuelve el usuario correspondiente al `idusuario` solicitado. Por un tema de seguridad para poder listar los usuarios debe estar identificado a través de un tokenJWT de autorización.

---

- **POST** `<<BaseUrl>>/api/usuarios`  
  Authorization: Bearer tokenJWT  
  Crea un nuevo usuario con la información en formato JSON que se pone en el body. Luego de insertar se devuelve el idusuario insertado. Debe notarse que para poder crear un nuevo usuario e insertarlo en la base de datos, primero debe estar identificado a través de un token de autorización.

  Ej:  
  ```
     {  
        "nombreusuario": "webadmin",  
        "password": "admin"  
     }  
  ```
---

- **PUT** `<<BaseUrl>>/api/usuarios/:idusuario`  
  Authorization: Bearer tokenJWT  
  Modifica la clave del usuario correspondiente al `idusuario`. La información a modificar se pone en formato JSON en el body (al igual que el POST). El campo es la password. Debe notarse que para poder modificar un autor, primero debe estar identificado a través de un token de autorización.  

  Ej:  
```
     {  
        "password": "admin"  
     }  
  ```
---

- **DELETE** `<<BaseUrl>>/api/usuarios/:idusuario`  
  Authorization: Bearer tokenJWT  
  Elimina el usuario correspondiente al `idusuario`. Debe notarse que para poder eliminar un autor, primero debe estar identificado a través de un token de autorización. También observe que si el usuario es el mismo del usuario que está trabajando, o si fuera el último usuario que queda, no se podrá borrar el usuario.





---



# Nota de los autores
Hemos puesto mucho esfuerzo en esta aplicación. Esperamos que le resulte agradable y fácil su uso. Cualquier comentario, sugerencia o corrección que considere pertinente, estamos a su entera disposición.
