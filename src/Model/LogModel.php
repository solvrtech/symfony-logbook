<?php

namespace Solvrtech\Logbook\Model;

class LogModel
{
    public ?string $message = null;

    public ?string $file = null;

    public ?array $stackTrace = null;

    public ?int $code = null;

    public ?string $level = null;

    public ?string $channel = null;

    public ?string $datetime = null;

    public ?array $additional = null;

    public ?ClientModel $client = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getStackTrace(): ?array
    {
        return $this->stackTrace;
    }

    public function setStackTrace(?array $stackTrace): self
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getDatetime(): ?string
    {
        return $this->datetime;
    }

    public function setDatetime(?string $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getAdditional(): ?array
    {
        return $this->additional;
    }

    public function setAdditional(?array $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    public function getClient(): ?ClientModel
    {
        return $this->client;
    }

    public function setClient(?ClientModel $client): self
    {
        $this->client = $client;

        return $this;
    }
}
