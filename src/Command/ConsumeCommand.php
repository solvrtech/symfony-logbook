<?php

namespace Solvrtech\Logbook\Command;

use Exception;
use Solvrtech\Logbook\Exception\RuntimeException;
use Solvrtech\Logbook\Transport\AsyncTransportInterface;
use Solvrtech\Logbook\Transport\Doctrine\DoctrineTransport;
use Solvrtech\Logbook\Transport\Redis\RedisTransport;
use Solvrtech\Logbook\Transport\TransportInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'logbook:log:consume',
    description: 'Consume log',
)]
class ConsumeCommand extends Command
{
    private TransportInterface $transport;

    public function __construct(
        TransportInterface $transport
    ) {
        $this->transport = $transport;

        parent::__construct();
    }

    protected function configure(): void
    {
        // configuration
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        while (true) {
            if (!$this->transport instanceof AsyncTransportInterface) {
                sleep(60);
                continue;
            }

            [$batch, $ids] = $this->transport->get();

            while (null !== $batch) {
                try {
                    self::send($batch, $ids);
                } catch (Exception $exception) {
                    throw new RuntimeException($exception->getMessage(), 0, $exception);
                }

                [$batch, $ids] = $this->transport->get();
            }

            // If using doctrine transport, delete all logs after sending them into the logbook
            if ($this->transport instanceof DoctrineTransport) {
                $this->transport->ack();
            }

            sleep(60);
        }

        return Command::SUCCESS;
    }

    /**
     * Try to send the batch of logs into the logbook, and mark it as sent.
     *
     * @param array $batch
     * @param array $ids
     */
    private function send(array $batch, array $ids): void
    {
        // Get the headers and unset them from the batch
        $headers = $batch['headers'];
        unset($batch['headers']);
        $apiUrl = $headers['url'];
        unset($headers['url']);

        $httpClient = HttpClient::create();

        try {
            $response = $httpClient->request(
                'POST',
                "{$apiUrl}/api/log/save/batch",
                [
                    'headers' => $headers,
                    'body' => json_encode($batch),
                ]
            );
        } catch (Exception|TransportExceptionInterface $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }

        // If using redis transport, delete all sent logs
        if ($this->transport instanceof RedisTransport) {
            $this->transport->ack($ids);
        }
    }
}
