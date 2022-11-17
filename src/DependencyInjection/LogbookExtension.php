<?php

namespace Solvrtech\Symfony\Logbook\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
            $loader->load('logbook.xml');

            $this->setLogbookAPI($config['api']);

            $handler = $this->buildHandler($container);
            $container->setParameter('logbook.handler', $handler);
            $container->setParameter('logbook.logbook_api', $this->logbookAPI);
        }
        $container->registerForAutoconfiguration(PsrLogMessageProcessor::class)->addTag('logbook.processor');
    }

    /**
     * Build logbook handler
     * 
     * @param ContainerBuilder $container
     * @param array $config
     * 
     * @return string
     */
    public function buildHandler(ContainerBuilder $container): string
    {
        $definition = new Definition('Solvrtech\Symfony\Logbook\Handler\LogbookHandler');
        $definition->setArguments([
            $this->logbookAPI['url'],
            $this->logbookAPI['key']
        ]);

        $id = 'logbook.handler';
        $container->setDefinition($id, $definition);

        return $id;
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
}
