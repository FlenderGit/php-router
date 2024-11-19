<?php

namespace Flender\PhpRouter;

class JWT
{
    
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    static function encode(array $payload, string $key): string
    {
        $jwt = new JWT($key);
        return $jwt->_encode($payload);
    }

    static function decode(string $jwt, string $key): array
    {
        $t_jwt = new JWT($key);
        return $t_jwt->_decode($jwt);
    }

    private function _decode(string $jwt): array
    {
        $jwt = explode(".", $jwt);
        if (count($jwt) !== 3) {
            throw new \Exception("Invalid JWT");
        }

        $signature = hash_hmac("sha256", $jwt[0] . "." . $jwt[1], $this->key, true);
        $signature_from_token = $this->base64URLDecode($jwt[2]);

        if (!hash_equals($signature, $signature_from_token)) {
            throw new \Exception("Invalid signature");
        }
        
        $payload = json_decode(base64_decode($jwt[1]), true);

        if ($payload['exp'] < time()) {
            throw new \Exception("Token expired");
        }

        return $payload;
    }

    private function _encode(array $payload): string
    {

        $header = json_encode([
            "alg" => "HS256",
            "typ" => "JWT"
        ]);

        $header = $this->base64URLEncode($header);
        $payload = json_encode($payload);
        $payload = $this->base64URLEncode($payload);

        $signature = hash_hmac("sha256", $header . "." . $payload, $this->key, true);
        $signature = $this->base64URLEncode($signature);
        return $header . "." . $payload . "." . $signature;
    }


    private function base64URLEncode(string $text): string
    {

        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    private function base64URLDecode(string $text): string
    {
        return base64_decode(
            str_replace(
                ["-", "_"],
                ["+", "/"],
                $text
            )
        );
    }
    

}