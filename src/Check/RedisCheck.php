<?php

namespace Solvrtech\Logbook\Check;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Solvrtech\Logbook\Exception\LogbookHealthException;
use Solvrtech\Logbook\Model\ConditionModel;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisCheck extends CheckService
{
    private CacheItemPoolInterface $cachePool;

    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ConditionModel
    {
        $condition = new ConditionModel();

        try {
            $redis = self::getRedisSize();

            $condition->setStatus(ConditionModel::OK)
                ->setMeta([
                    'redisSize' => $redis,
                    'unit' => 'Mb',
                ]);
        } catch (Exception $exception) {
        }

        return $condition;
    }

    /**
     *  Get the size of Redis cache in megabytes
     *
     * @return array
     *
     * @throws LogbookHealthException|\RedisException
     */
    private function getRedisSize(): array
    {
        $pool = $this->cachePool->getPool();

        if (!$pool instanceof RedisAdapter) {
            throw new LogbookHealthException();
        }

        $redisInfo = self::checkRedisConnection($pool);

        return [
            'size' => round($redisInfo['used_memory'] / 1048576, 2),
        ];
    }

    /**
     * Check the Redis connection and return the Redis info.
     *
     * @param RedisAdapter $redis
     * @return array
     *
     * @throws LogbookHealthException
     * @throws \RedisException
     */
    private function checkRedisConnection(RedisAdapter $redis): array
    {
        foreach ((array)$redis as $key => $val) {
            if ($val instanceof \Redis ||
                $val instanceof \RedisArray ||
                $val instanceof \RedisCluster ||
                $val instanceof \Predis\ClientInterface ||
                $val instanceof \Relay\Relay
            ) {
                return $val->info('memory');
            }
        }

        throw new LogbookHealthException();
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'redis';
    }
}