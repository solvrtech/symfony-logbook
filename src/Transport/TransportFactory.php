<?php

namespace Solvrtech\Logbook\Transport;

use Solvrtech\Logbook\Exception\InvalidArgumentException;

class TransportFactory
{
    private iterable $factories;

    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    /**
     * Create a log transport instance based on the provided Data Source Name (DSN).
     *
     * @param string $dsn
     *
     * @return TransportInterface
     */
    public function fromDsn(string $dsn): TransportInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return $factory->create($dsn);
            }
        }

        throw new InvalidArgumentException(sprintf('No transport supports the given DSN "%s".', $dsn));
    }
}