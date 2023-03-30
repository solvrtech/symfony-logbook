<?php

namespace Solvrtech\Symfony\Logbook\DependencyInjection;

use Exception;
use ReflectionException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

class LogbookExtension extends Extension implements PrependExtensionInterface
{
    private array $logbookAPI;
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

        if (isset($config['api'])) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('logbook.xml');

            $this->setLogbookAPI($config['api']);
            $this->buildHandler($container);
            $this->buildProcessor($container);
        }
    }

    private function setLogbookAPI(array $config): self
    {
        foreach ($config as $key => $val) {
            if ('url' === $key) {
                // check if the last character is a slash and then remove that one
                if ('/' === substr($val, -1)) {
                    $val = substr($val, 0, -1);
                }
            }

            $this->logbookAPI[$key] = $val;
        }

        return $this;
    }

    /**
     * Build logbook handler
     *
     * @param ContainerBuilder $container
     */
    public function buildHandler(ContainerBuilder $container)
    {
        $id = 'monolog.handler.logbook';

        $definition = new Definition('Solvrtech\Symfony\Logbook\Handler\LogbookHandler');
        $definition->setArguments([
            $this->logbookAPI['url'],
            $this->logbookAPI['key'],
            $this->handler['level'],
            $this->getAppVersion($container),
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
     * Build logbook processor
     *
     * @param ContainerBuilder $container
     */
    public function buildProcessor(ContainerBuilder $container)
    {
        $id = 'logbook.processor';

        $definition = new Definition('Solvrtech\Symfony\Logbook\Processor\LogbookProcessor');
        $container->setParameter($id, $id);
        $container->setDefinition($id, $definition);
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
