<?php

namespace Solvrtech\Logbook\Transport;

use InvalidArgumentException;

class Dsn
{
    private string $scheme;
    private string $host;
    private ?string $user;
    private ?string $password;
    private ?int $port;
    private array $options;

    public function __construct(
        string $scheme,
        string $host,
        string $user = null,
        #[\SensitiveParameter] string $password = null,
        int $port = null,
        array $options = []
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->options = $options;
    }

    /**
     * Create a DSN object from the given DSN string and check its validity.
     *
     * @param string $dsn
     *
     * @return Dsn
     */
    public static function fromString(string $dsn): self
    {
        if (false === $parsedDsn = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The "%s" DSN is invalid.', $dsn));
        }

        if (!isset($parsedDsn['scheme'])) {
            throw new InvalidArgumentException(sprintf('The "%s" DSN must contain a scheme.', $dsn));
        }

        if (!isset($parsedDsn['host'])) {
            throw new InvalidArgumentException(
                sprintf('The "%s" DSN must contain a host (use "default" by default).', $dsn)
            );
        }

        $user = '' !== ($parsedDsn['user'] ?? '') ? urldecode($parsedDsn['user']) : null;
        $password = '' !== ($parsedDsn['pass'] ?? '') ? urldecode($parsedDsn['pass']) : null;
        $port = $parsedDsn['port'] ?? null;
        parse_str($parsedDsn['query'] ?? '', $query);

        return new self($parsedDsn['scheme'], $parsedDsn['host'], $user, $password, $port, $query);
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPort(int $default = null): ?int
    {
        return $this->port ?? $default;
    }

    public function getOption(string $key, mixed $default = null)
    {
        return $this->options[$key] ?? $default;
    }
}