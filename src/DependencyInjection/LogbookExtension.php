<?php

namespace Solvrtech\Symfony\Logbook\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

class LogbookExtension extends Extension
{
    private array $logbookAPI;

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['api'])) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('logbook.xml');

            $this->setLogbookAPI($config['api']);

            $container->setParameter('logbook.handler', $this->buildHandler($config['level'], $container));
            $container->setParameter('logbook.logbook_api', $this->logbookAPI);
            $container->setParameter('logbook.app_version', $this->getAppVersion($container));
        }

        $container->setParameter('logbook.processor', $this->buildProcessor($container));
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
     * @param string $minLevel
     * @param ContainerBuilder $container
     *
     * @return string
     */
    public function buildHandler(string $minLevel, ContainerBuilder $container): string
    {
        $definition = new Definition('Solvrtech\Symfony\Logbook\Handler\LogbookHandler');
        $definition->setArguments([
            $this->logbookAPI['url'],
            $this->logbookAPI['key'],
            $minLevel,
            $this->getAppVersion($container),
        ]);

        $id = 'logbook.handler';
        $container->setDefinition($id, $definition);

        return $id;
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
     *
     * @return string
     */
    public function buildProcessor(ContainerBuilder $container): string
    {
        $definition = new Definition('Solvrtech\Symfony\Logbook\Processor\LogbookProcessor');
        $id = 'logbook.processor';
        $container->setDefinition($id, $definition);

        return $id;
    }
}
