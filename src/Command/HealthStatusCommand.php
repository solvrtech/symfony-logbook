<?php

namespace Solvrtech\Symfony\Logbook\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'logbook:health',
    description: 'Send client app health status to logbook app',
)]
class HealthStatusCommand extends Command
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    protected function configure(): void
    {
        // configuration
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $httpClient = HttpClient::create();

        if ($this->parameterBag->has('logbook.logbook_api')) {
            $logbookApi = $this->parameterBag->get('logbook.logbook_api');
            $appVersion = $this->parameterBag->get('version');

            try {
                $httpClient->request(
                    'POST',
                    "{$logbookApi['url']}/api/health-status",
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'x-lb-token' => $logbookApi['key'],
                            'x-lb-version' => $appVersion,
                        ],
                    ]
                );
            } catch (Exception $e) {
            }
        }

        return Command::SUCCESS;
    }
}
