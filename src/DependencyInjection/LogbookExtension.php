<?php

namespace Solvrtech\Logbook\DependencyInjection;

use Exception;
use ReflectionException;
use Solvrtech\Logbook\Command\LogConsumeCommand;
use Solvrtech\Logbook\Model\LogbookConfig;
use Solvrtech\Logbook\Transport\TransportInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

class LogbookExtension extends Extension implements PrependExtensionInterface
{
    private LogbookConfig $logbookConfig;
    private array $handler;

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->setLogbookConfig($config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('logbook.yaml');

        $this->buildTransport($container);

        if (!empty($this->handler)) {
            $this->buildHandler($container);
            $this->buildProcessor($container);
        }

        $this->buildAuthenticator($container);
        $this->buildHealthService($container);
    }

    private function setLogbookConfig(array $config): self
    {
        $logbookConfig = (new LogbookConfig())
            ->setInstanceId($config['instance_id'])
            ->setApiKey($config['api']['key'])
            ->setTransport($config['transport']);

        // check if the last character is a slash and then remove that one
        $url = ('/' === substr($config['api']['url'], -1)) ?
            substr($config['api']['url'], 0, -1) :
            $config['api']['url'];

        $logbookConfig->setApiUrl($url);
        $this->logbookConfig = $logbookConfig;

        return $this;
    }

    /**
     * Build log transport to handle logs
     *
     * @param ContainerBuilder $container
     */
    public function buildTransport(ContainerBuilder $container)
    {
        $definition = (new Definition(TransportInterface::class))
            ->setFactory([new Reference('logbook.transport.factory'), 'fromDsn'])
            ->setArguments([$this->logbookConfig->getTransport()]);

        $transportId = 'logbook.transport';
        $container->setDefinition($transportId, $definition);

        if ($container->hasDefinition('Solvrtech\Logbook\Command\ConsumeCommand')) {
            $consumeCommandDefinition = $container->getDefinition('Solvrtech\Logbook\Command\ConsumeCommand');
            $consumeCommandDefinition->replaceArgument(0, new Reference($transportId));
        }
    }

    /**
     * Build LogBook handler
     *
     * @param ContainerBuilder $container
     */
    public function buildHandler(ContainerBuilder $container)
    {
        $id = 'monolog.handler.logbook';

        $config = $this->logbookConfig;
        $config->offsetSet("appVersion", $this->getAppVersion($container));

        $definition = new Definition('Solvrtech\Logbook\Handler\LogbookHandler');
        $definition->setArguments([
            $this->handler['level'],
            $config->toArray(),
            new Reference('logbook.transport'),

        ]);

        $container->setParameter($id, $id);
        $container->setDefinition($id, $definition);
    }

    /**
     * Get app and framework version.
     *
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function getAppVersion(ContainerBuilder $container): string
    {
        $version = [
            'core' => "Symfony v".Kernel::VERSION,
        ];

        if ($container->hasParameter('version')) {
            $appVersion = $container->getParameter('version');
            $version['app'] = is_string($appVersion) ? $appVersion : '';
        }

        return json_encode($version);
    }

    /**
     * Build LogBook processor
     *
     * @param ContainerBuilder $container
     */
    public function buildProcessor(ContainerBuilder $container)
    {
        $id = 'logbook.processor';

        $definition = new Definition('Solvrtech\Logbook\Processor\LogbookProcessor');
        $container->setParameter($id, $id);
        $container->setDefinition($id, $definition);
    }

    /**
     * Build Logbook authenticator
     *
     * @param ContainerBuilder $container
     */
    public function buildAuthenticator(ContainerBuilder $container)
    {
        $id = 'logbook.authenticator';

        $definition = new Definition('Solvrtech\Logbook\Security\LogbookAuthenticator');
        $definition->setArguments([
            $this->logbookConfig->getApiKey(),
        ]);
        $container->setParameter($id, $id);
        $container->setDefinition($id, $definition);
    }

    /**
     * Build LogbookHealth service
     *
     * @param ContainerBuilder $container
     */
    public function buildHealthService(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('logbook_health_service');
        $definition->replaceArgument(
            1,
            $this->logbookConfig->getInstanceId()
        );
    }

    /**
     * {@inheritDoc}
     * {monologConfiguration}
     *
     * @throws ReflectionException
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('monolog');
        $monologConfiguration = $this->getMonologConfiguration($container);
        $config = $this->processConfiguration($monologConfiguration, $configs);

        if (isset($config['handlers'])) {
            $this->handler = $config['handlers']['logbook'] ?? [];
        }
    }

    /**
     * Get monolog bundle configuration
     *
     * @param ContainerBuilder $containerBuilder
     *
     * @return Configuration|null
     *
     * @throws ReflectionException
     */
    private function getMonologConfiguration(ContainerBuilder $containerBuilder): ConfigurationInterface|null
    {
        $class = "Symfony\Bundle\MonologBundle\DependencyInjection\Configuration";
        $class = $containerBuilder->getReflectionClass($class);

        return $class->newInstance();
    }
}
