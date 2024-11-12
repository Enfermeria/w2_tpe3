<?php
    require_once 'config.php';

    function createJWT($payload) {
        // Header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        // Payload
        $payload = json_encode($payload);

        // Base64Url
        $header = base64_encode($header);
        $header = str_replace(['+', '/', '='], ['-', '_', ''], $header);
        $payload = base64_encode($payload);
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload);

        // Firma
        $signature = hash_hmac('sha256', $header . "." . $payload, MI_CLAVE_JWT, true);
        $signature = base64_encode($signature);
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], $signature);

        // JWT
        $jwt = $header . "." . $payload . "." . $signature;
        return $jwt;
    }

    function validateJWT($jwt) {
        //$jwt es header.payload.signature
        $jwt = explode('.', $jwt); // jwt es [header, payload, signature]
        if(count($jwt) != 3) {
            return null;
        }
        $header = $jwt[0];
        $payload = $jwt[1];
        $signature = $jwt[2];

        //calculo la signatura
        $valid_signature = hash_hmac('sha256', $header . "." . $payload, MI_CLAVE_JWT, true);
        $valid_signature = base64_encode($valid_signature);
        $valid_signature = str_replace(['+', '/', '='], ['-', '_', ''], $valid_signature);

        if($signature != $valid_signature) { // si la signatura no es válida
            return null;
        }

        $payload = base64_decode($payload);
        $payload = json_decode($payload);

        if($payload->exp < time()) { // verifico que el token no se haya vencido
            return null;
        }

        return $payload;
    }