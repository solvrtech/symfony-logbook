<?php

namespace Solvrtech\Logbook\Transport\Doctrine;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Result as DriverResult;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\InvalidLockMode;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Types\Types;
use Solvrtech\Logbook\Exception\InvalidArgumentException;
use Solvrtech\Logbook\Exception\TransportException;
use Solvrtech\Logbook\Transport\ConnectionInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Throwable;
use function count;
use const FILTER_VALIDATE_BOOL;

class Connection implements ConnectionInterface
{
    protected const TABLE_OPTION_NAME = '_logbook_log_table_name';

    protected const DEFAULT_OPTIONS = [
        'table_name' => 'logbook_logs',
        'batch' => 15,
        'auto_setup' => true,
        'get_notify_timeout' => 0,
    ];

    protected $config = [];
    protected ManagerRegistry $managerRegistry;
    private bool $autoSetup;

    public function __construct(
        array $configuration,
        ManagerRegistry $managerRegistry
    ) {
        $this->config = array_replace_recursive(static::DEFAULT_OPTIONS, $configuration);
        $this->managerRegistry = $managerRegistry;
        $this->autoSetup = $this->config['auto_setup'];
    }

    public static function buildConfiguration(string $dsn): array
    {
        $opts = [];

        if (false === $components = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The given Doctrine DSN "%s" is invalid.', $dsn));
        }

        $query = [];
        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        $config = ['connection' => $components['host']];
        $config += $query + $opts + static::DEFAULT_OPTIONS;

        $config['auto_setup'] = filter_var($config['auto_setup'], FILTER_VALIDATE_BOOL);

        // check for extra keys in options
        $optsExtraKeys = array_diff(array_keys($opts), array_keys(static::DEFAULT_OPTIONS));
        if (0 < count($optsExtraKeys)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown option found: [%s]. Allowed options are [%s].',
                    implode(', ', $optsExtraKeys),
                    implode(', ', array_keys(static::DEFAULT_OPTIONS))
                )
            );
        }

        return $config;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DBALException
     * @throws TableNotFoundException
     */
    public function add(string $body, array $headers): string
    {
        $log = json_encode([
            'body' => $body,
            'headers' => $headers,
        ]);

        if (false === $log) {
            throw new TransportException(json_last_error_msg());
        }

        $queryBuilder = $this->getDriverConnection()->createQueryBuilder()
            ->insert($this->config['table_name'])
            ->values([
                'log' => '?',
            ]);

        $this->executeStatement(
            $queryBuilder->getSQL(),
            [
                $log,
            ],
            [
                null,
            ]
        );

        return $this->getDriverConnection()->lastInsertId();
    }

    private function createQueryBuilder(string $alias = 'l'): QueryBuilder
    {
        $queryBuilder = $this->getDriverConnection()->createQueryBuilder()
            ->from($this->config['table_name'], $alias);

        $alias .= '.';

        return $queryBuilder->select(
            str_replace(
                ', ',
                ', '.$alias,
                $alias.'id AS "id", log AS "log"'
            )
        );
    }

    public function getDriverConnection(): DBALConnection
    {
        return $this->managerRegistry
            ->getConnection(
                $this->config['connection']
            );
    }

    /**
     * @throws TableNotFoundException
     * @throws DBALException
     */
    protected function executeStatement(string $sql, array $parameters = [], array $types = []): int|string
    {
        $driver = $this->getDriverConnection();

        try {
            if (method_exists($driver, 'executeStatement')) {
                $stmt = $driver->executeStatement($sql, $parameters, $types);
            } else {
                $stmt = $driver->executeUpdate($sql, $parameters, $types);
            }
        } catch (TableNotFoundException $e) {
            if ($driver->isTransactionActive()) {
                throw $e;
            }

            // create table
            if ($this->autoSetup) {
                $this->setup();
            }
            if (method_exists($driver, 'executeStatement')) {
                $stmt = $driver->executeStatement($sql, $parameters, $types);
            } else {
                $stmt = $driver->executeUpdate($sql, $parameters, $types);
            }
        }

        return $stmt;
    }

    /**
     * @throws TableNotFoundException
     * @throws DBALException
     */
    public function setup(): void
    {
        $driver = $this->getDriverConnection();

        $configuration = $driver->getConfiguration();
        $assetFilter = $configuration->getSchemaAssetsFilter();
        $configuration->setSchemaAssetsFilter(null);
        $this->updateSchema();
        $configuration->setSchemaAssetsFilter($assetFilter);
        $this->autoSetup = false;
    }

    /**
     * @throws DBALException
     */
    private function updateSchema(): void
    {
        $schemaManager = $this->createSchemaManager();
        $comparator = $this->createComparator($schemaManager);
        $schemaDiff = $this->compareSchemas($comparator, $schemaManager->createSchema(), $this->getSchema());
        $driver = $this->getDriverConnection();

        foreach ($schemaDiff->toSaveSql($driver->getDatabasePlatform()) as $sql) {
            if (method_exists($driver, 'executeStatement')) {
                $driver->executeStatement($sql);
            } else {
                $driver->exec($sql);
            }
        }
    }

    /**
     * @throws DBALException
     */
    private function createSchemaManager(): AbstractSchemaManager
    {
        $driver = $this->getDriverConnection();

        return method_exists($driver, 'createSchemaManager')
            ? $driver->createSchemaManager()
            : $driver->getSchemaManager();
    }

    private function createComparator(AbstractSchemaManager $schemaManager): Comparator
    {
        return method_exists($schemaManager, 'createComparator')
            ? $schemaManager->createComparator()
            : new Comparator();
    }

    private function compareSchemas(Comparator $comparator, Schema $from, Schema $to): SchemaDiff
    {
        return method_exists($comparator, 'compareSchemas')
            ? $comparator->compareSchemas($from, $to)
            : $comparator->compare($from, $to);
    }

    private function getSchema(): Schema
    {
        $schema = new Schema([], [], $this->createSchemaManager()->createSchemaConfig());
        $this->addTableToSchema($schema);

        return $schema;
    }

    private function addTableToSchema(Schema $schema): void
    {
        $table = $schema->createTable($this->config['table_name']);
        // add an internal option to mark that we created this & the non-namespaced table name
        $table->addOption(self::TABLE_OPTION_NAME, $this->config['table_name']);
        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setNotnull(true);
        $table->addColumn('log', Types::TEXT)
            ->setNotnull(true);
        $table->addColumn('sent_at', Types::DATETIME_MUTABLE)
            ->setNotnull(false);
        $table->setPrimaryKey(['id']);;
    }

    /**
     * {@inheritDoc}
     *
     * @throws TableNotFoundException
     * @throws Throwable
     * @throws InvalidLockMode
     * @throws Exception
     * @throws DBALException
     */
    public function get(): ?array
    {
        $driver = $this->getDriverConnection();

        get:
        $driver->beginTransaction();
        try {
            $query = $this->createLogQueryBuilder()
                ->where('sent_at IS NULL')
                ->setMaxResults($this->config['batch']);
            $sql = $query->getSQL();

            // Append pessimistic write lock to FROM clause if db platform supports it
            if (
                ($fromPart = $query->getQueryPart('from')) &&
                ($table = $fromPart[0]['table'] ?? null) &&
                ($alias = $fromPart[0]['alias'] ?? null)
            ) {
                $fromClause = sprintf('%s %s', $table, $alias);
                $sql = str_replace(
                    sprintf('FROM %s WHERE', $fromClause),
                    sprintf(
                        'FROM %s WHERE',
                        $driver->getDatabasePlatform()->appendLockHint(
                            $fromClause,
                            LockMode::PESSIMISTIC_WRITE
                        )
                    ),
                    $sql
                );
            }

            // use SELECT ... FOR UPDATE to lock table
            $stmt = $this->executeQuery(
                $sql.' '.$driver->getDatabasePlatform()->getWriteLockSQL(),
                $query->getParameters(),
                $query->getParameterTypes()
            );
            $doctrineLog = $stmt instanceof Result || $stmt instanceof DriverResult ?
                $stmt->fetchAllAssociative() :
                $stmt->fetchAll();

            if (false === $doctrineLog) {
                $driver->commit();

                return null;
            }

            [$batch, $ids] = $this->decodeLogsResult($doctrineLog);
            $this->markAsProcessed($ids);
            $driver->commit();

            return [$batch, $ids];
        } catch (Throwable $e) {
            $driver->rollBack();

            if ($this->autoSetup && $e instanceof TableNotFoundException) {
                $this->setup();
                goto get;
            }

            throw $e;
        }
    }

    private function createLogQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder();
    }

    /**
     * @throws TableNotFoundException
     * @throws DBALException
     */
    private function executeQuery(string $sql, array $parameters = [], array $types = []): Result
    {
        $driver = $this->getDriverConnection();

        try {
            $stmt = $driver->executeQuery($sql, $parameters, $types);
        } catch (TableNotFoundException $e) {
            if ($driver->isTransactionActive()) {
                throw $e;
            }

            // create table
            if ($this->autoSetup) {
                $this->setup();
            }
            $stmt = $driver->executeQuery($sql, $parameters, $types);
        }

        return $stmt;
    }

    private function decodeLogsResult(array $logs): array
    {
        $batch = [];
        $ids = [];

        foreach ($logs as $key => $log) {
            $ids[] = $log['id'];
            $batch[$key] = [
                'id' => $log['id'],
                'data' => json_decode($log['log'], true),
            ];
        }

        return [$batch, $ids];
    }

    /**
     * @throws TableNotFoundException
     * @throws DBALException
     */
    private function markAsProcessed(array $ids): void
    {
        $driver = $this->getDriverConnection();

        $queryBuilder = $driver->createQueryBuilder()
            ->update($this->config['table_name'])
            ->set('sent_at', '?');
        $queryBuilder->where($queryBuilder->expr()->in('id', $ids));
        $now = new \DateTimeImmutable();
        $this->executeStatement($queryBuilder->getSQL(), [
            $now,
        ], [
            Types::DATETIME_MUTABLE,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function ack(?array $ids = null): void
    {
        $driver = $this->getDriverConnection();

        $queryBuilder = $driver->createQueryBuilder()
            ->where('sent_at IS NOT NULL')
            ->delete($this->config['table_name'], 'l');

        try {
            $this->executeStatement($queryBuilder->getSQL());
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }
}