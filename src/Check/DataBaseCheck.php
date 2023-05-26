<?php

namespace Solvrtech\Logbook\Check;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Solvrtech\Logbook\Exception\LogbookHealthException;
use Solvrtech\Logbook\Model\ConditionModel;

class DataBaseCheck extends CheckService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'database';
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ConditionModel
    {
        $condition = new ConditionModel();

        try {
            $database = self::checkSize();

            $condition->setStatus(ConditionModel::OK)
                ->setMeta([
                    'databaseSize' => $database,
                    'unit' => 'Mb',
                ]);
        } catch (Exception $exception) {
        }

        return $condition;
    }

    /**
     * Checks the size of the database by counting the number of records.
     *
     * @return array
     *
     * @throws LogbookHealthException|\Doctrine\DBAL\Exception
     */
    private function checkSize(): array
    {
        $connection = $this->entityManager->getConnection();

        if (self::checkConnection($connection)) {
            $connectionParam = $connection->getParams();

            return match ($connectionParam['driver']) {
                "pdo_pgsql" => self::checkPostgresSize($connection, $connection->getDatabase()),
                default => self::checkMySqlSize($connection, $connection->getDatabase()),
            };
        }

        throw new LogbookHealthException();
    }

    /**
     * Checks the database connection is working properly.
     *
     * @param Connection $connection
     *
     * @return bool
     *
     * @throws LogbookHealthException
     */
    private function checkConnection(Connection $connection): bool
    {
        try {
            $connection->connect();
        } catch (Exception $exception) {
            throw new LogbookHealthException();
        }

        return true;
    }

    /**
     * Checks the size of the Postgres database.
     *
     * @param Connection $connection
     * @param string $dbName
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function checkPostgresSize(Connection $connection, string $dbName): array
    {
        $query = "SELECT ROUND(pg_database_size(:dbName)/ 1048576, 2) as db_size";
        $params = [
            'dbName' => $dbName,
        ];

        $result = $connection
            ->executeQuery($query, $params)
            ->fetchAssociative();

        return [
            'default' => $result['db_size'],
        ];
    }

    /**
     * Checks the size of the MySql database.
     *
     * @param Connection $connection
     * @param string $dbName
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function checkMySqlSize(Connection $connection, string $dbName): array
    {
        $query = "SELECT table_schema :dbName, ROUND(SUM(data_length + index_length) / 1048576, 2) as size FROM information_schema.tables GROUP BY table_schema";
        $params = [
            'dbName' => $dbName,
        ];

        $result = $connection
            ->executeQuery($query, $params)
            ->fetchAssociative();

        return [
            'default' => array_sum(array_column($result, 'size')),
        ];
    }
}
