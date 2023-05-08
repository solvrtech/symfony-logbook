<?php

namespace Solvrtech\Logbook\DependencyInjection\Compiler;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function method_exists;

class LoggerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('monolog.handler.logbook')) {
            $this->createLogger($container);

            // push handler
            $logger = $container->getDefinition('monolog.logger.logbook');
            $logger->addMethodCall('pushHandler', [
                new Reference($container->getParameter('monolog.handler.logbook')),
            ]);

            // push processor
            $definition = $container->findDefinition('monolog.handler.logbook');
            $definition->addMethodCall('pushProcessor', [
                new Reference($container->getParameter('logbook.processor')),
            ]);
        }
    }

    /**
     * Create new logger from the monolog.logger_prototype
     *
     * @param ContainerBuilder $container
     */
    protected function createLogger(ContainerBuilder $container)
    {
        $id = 'monolog.logger.logbook';
        $logger = new ChildDefinition('monolog.logger_prototype');
        $container->setDefinition($id, $logger);

        if (method_exists($container, 'registerAliasForArgument')) {
            $container->registerAliasForArgument($id, LoggerInterface::class);
        }
    }
}
