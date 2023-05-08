<?php

namespace Solvrtech\Logbook\Model;

class ConditionModel
{
    public const OK = "ok";
    public const FAILED = "failed";

    public ?string $key = null;
    public ?string $status = self::FAILED;
    public array $meta = [];

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
