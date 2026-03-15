<?php

namespace App\Services;

use RuntimeException;

class JwtService
{
    protected string $secret;
    protected int $ttl; // seconds

    public function __construct()
    {
        $this->secret = config('app.key');
        $this->ttl    = 60 * 60 * 24 * 30; // 30 days
    }

    /** Encode payload into a JWT string. */
    public function encode(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->ttl;

        $header    = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body      = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$body", $this->secret, true));

        return "$header.$body.$signature";
    }

    /** Decode and verify a JWT. Throws RuntimeException on failure. */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT format.');
        }

        [$header, $body, $sig] = $parts;

        $expected = $this->base64UrlEncode(hash_hmac('sha256', "$header.$body", $this->secret, true));
        if (!hash_equals($expected, $sig)) {
            throw new RuntimeException('JWT signature mismatch.');
        }

        $payload = json_decode($this->base64UrlDecode($body), true);
        if (!$payload) {
            throw new RuntimeException('JWT payload decode failed.');
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new RuntimeException('JWT has expired.');
        }

        return $payload;
    }

    /** Returns true if the string looks like a JWT (has 2 dots). */
    public function isJwt(string $token): bool
    {
        return substr_count($token, '.') === 2;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}
