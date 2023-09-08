<?php

namespace Solvrtech\Logbook\Transport\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Solvrtech\Logbook\Transport\TransportFactoryInterface;
use Solvrtech\Logbook\Transport\TransportInterface;

class DoctrineTransportFactory implements TransportFactoryInterface
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $dsn): TransportInterface
    {
        $configuration = Connection::buildConfiguration($dsn);
        $connection = new Connection($configuration, $this->registry);

        return new DoctrineTransport($connection);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $dsn): bool
    {
        return str_starts_with($dsn, 'doctrine://');
    }
}
