<?php

namespace Solvrtech\Logbook\Check;

use Exception;
use Ketut\RandomString\Random;
use Solvrtech\Logbook\Model\ConditionModel;
use Symfony\Contracts\Cache\CacheInterface;

class CacheCheck extends CheckService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ConditionModel
    {
        $condition = new ConditionModel();

        try {
            $status = self::canStoringItem() ?
                ConditionModel::OK :
                ConditionModel::FAILED;

            $condition->setStatus($status);
        } catch (Exception $exception) {
        }

        return $condition;
    }

    /**
     * Try to save an item in the cache.
     *
     * @return bool
     *
     * @throws Exception
     */
    private function canStoringItem(): bool
    {
        $expectedValue = (new Random)->lowercase()->length(5)->generate();
        $key = 'logbook-health.check.'.self::getKey();

        // create a new item by trying to get it from the cache
        $actualValue = $this->cache->getItem($key);

        // assign a value to the item and save it
        $actualValue->set($expectedValue);
        $this->cache->save($actualValue);

        // retrieve the cache item
        $actualValue = $this->cache->getItem($key);
        if (!$actualValue->isHit()) {
            return false;
        }

        // retrieve the value stored by the item
        return $actualValue->get() === $expectedValue;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'cache';
    }
}