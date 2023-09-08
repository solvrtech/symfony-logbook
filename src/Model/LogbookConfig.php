<?php

namespace Solvrtech\Logbook\Model;

use ArrayAccess;

class LogbookConfig implements ArrayAccess
{
    private $container = [
        "apiUrl" => null,
        "apiKey" => null,
        "instanceId" => null,
        "transport" => null,
    ];

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->container[$offset]);
    }

    public function getApiUrl(): ?string
    {
        return $this->offsetGet("apiUrl");
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function setApiUrl(?string $apiUrl): self
    {
        $this->offsetSet("apiUrl", $apiUrl);

        return $this;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function getApiKey(): ?string
    {
        return $this->offsetGet("apiKey");
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->offsetSet("apiKey", $apiKey);

        return $this;
    }

    public function getInstanceId(): ?string
    {
        return $this->offsetGet("instanceId");
    }

    public function setInstanceId(?string $instanceId): self
    {
        $this->offsetSet("instanceId", $instanceId);

        return $this;
    }

    public function getTransport(): ?string
    {
        return $this->offsetGet("transport");
    }

    public function setTransport(?string $transport): self
    {
        $this->offsetSet("transport", $transport);

        return $this;
    }

    public function toArray(): array
    {
        return $this->container;
    }
}