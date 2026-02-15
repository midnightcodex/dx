<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Str;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\UnencryptedToken;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;

class JwtService
{
    private Configuration $config;
    private SystemClock $clock;
    private DateTimeZone $timezone;

    public function __construct()
    {
        $secret = config('jwt.secret');
        if (empty($secret)) {
            throw new \RuntimeException('JWT secret is not configured. Set JWT_SECRET in .env');
        }

        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret)
        );

        $this->timezone = new DateTimeZone(config('jwt.timezone', 'UTC'));
        $this->clock = new SystemClock($this->timezone);
    }

    /**
     * Issue a signed access token for the given user.
     *
     * @return array{token: string, expires_in: int}
     */
    public function issueAccessToken(User $user): array
    {
        $now = new DateTimeImmutable('now', $this->timezone);
        $ttlMinutes = (int) config('jwt.access_ttl', 60);
        $expiresAt = $now->modify('+' . $ttlMinutes . ' minutes');

        $builder = $this->config->builder()
            ->issuedBy(config('jwt.issuer'))
            ->permittedFor(config('jwt.audience'))
            ->identifiedBy((string) Str::uuid())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->relatedTo($user->id)
            ->withClaim('org', $user->organization_id)
            ->withClaim('tv', (int) ($user->token_version ?? 1))
            ->withClaim('type', 'access');

        $token = $builder->getToken(
            $this->config->signer(),
            $this->config->signingKey()
        );

        return [
            'token' => $token->toString(),
            'expires_in' => $ttlMinutes * 60,
        ];
    }

    /**
     * Parse and validate an access token.
     */
    public function parseAccessToken(string $jwt): ?UnencryptedToken
    {
        try {
            $token = $this->config->parser()->parse($jwt);
        } catch (\Throwable) {
            return null;
        }

        if (!$token instanceof UnencryptedToken) {
            return null;
        }

        $leewaySeconds = (int) config('jwt.leeway', 60);
        $leeway = new DateInterval('PT' . max(0, $leewaySeconds) . 'S');

        $constraints = [
            new SignedWith($this->config->signer(), $this->config->signingKey()),
            new ValidAt($this->clock, $leeway),
        ];

        $issuer = config('jwt.issuer');
        if (!empty($issuer)) {
            $constraints[] = new IssuedBy($issuer);
        }

        $audience = config('jwt.audience');
        if (!empty($audience)) {
            $constraints[] = new PermittedFor($audience);
        }

        if (!$this->config->validator()->validate($token, ...$constraints)) {
            return null;
        }

        if ($token->claims()->get('type') !== 'access') {
            return null;
        }

        return $token;
    }
}
