<?php
    class JWTAuthMiddleware {
        public function run($req, $res) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION']; // "Bearer un.token.firma"
            $auth_header = explode(' ', $auth_header); // ["Bearer", "un.token.firma"]
            if(count($auth_header) != 2) {
                return;
            }
            if($auth_header[0] != 'Bearer') { // si el método no es Bearer, rebota
                return;
            }
            $jwt = $auth_header[1]; // un.token.firma
            $res->user = validateJWT($jwt);
        }
    }