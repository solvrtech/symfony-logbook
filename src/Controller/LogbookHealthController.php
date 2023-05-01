<?php

namespace Solvrtech\Symfony\Logbook\Controller;

use Solvrtech\Symfony\Logbook\Service\LogbookHealthService;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogbookHealthController
{
    private LogbookHealthService $service;

    public function __construct(LogbookHealthService $service)
    {
        $this->service = $service;
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse(
            $this->service->getResults()
        );
    }
}
