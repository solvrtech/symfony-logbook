<?php

namespace Solvrtech\Symfony\Logbook\DependencyInjection\Compiler;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Argument\BoundArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoggerCompilerPass implements CompilerPassInterface
{
    protected $channels = ['app'];

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('logbook.logger')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('monolog.logger') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['channel']) || 'app' === $tag['channel']) {
                    continue;
                }
            }

            $resolvedChannel = $container->getParameterBag()->resolveValue($tag['channel']);

            $definition = $container->getDefinition($id);
            $loggerId = sprintf('logbook.logger.%s', $resolvedChannel);
            $this->createLogger($resolvedChannel, $loggerId, $container);

            foreach ($definition->getArguments() as $index => $argument) {
                if ($argument instanceof Reference && 'logger' === (string) $argument) {
                    $definition->replaceArgument($index, $this->changeReference($argument, $loggerId));
                }
            }

            $calls = $definition->getMethodCalls();
            foreach ($calls as $i => $call) {
                foreach ($call[1] as $index => $argument) {
                    if ($argument instanceof Reference && 'logger' === (string) $argument) {
                        $calls[$i][1][$index] = $this->changeReference($argument, $loggerId);
                    }
                }
            }
            $definition->setMethodCalls($calls);

            if (\method_exists($definition, 'getBindings')) {
                $binding = new BoundArgument(new Reference($loggerId));

                // Mark the binding as used already, to avoid reporting it as unused if the service does not use a
                // logger injected through the LoggerInterface alias.
                $values = $binding->getValues();
                $values[2] = true;
                $binding->setValues($values);

                $bindings = $definition->getBindings();
                $bindings['Psr\Log\LoggerInterface'] = $binding;
                $definition->setBindings($bindings);
            }
        }

        // wire handler to channels
        $handler = $container->getParameter('logbook.handler');
        foreach ($this->channels as $channel) {
            try {
                $logger = $container->getDefinition(
                    $channel === 'app' ?
                        'logbook.logger' :
                        'logbook.logger.' . $channel
                );
            } catch (InvalidArgumentException $e) {
                throw new \InvalidArgumentException();
            }
            $logger->addMethodCall('pushHandler', [new Reference($handler)]);
        }
    }

    /**
     * Create new logger from the logbook.logger_prototype
     *
     * @param string $channel
     * @param string $loggerId
     * @param ContainerBuilder $container
     */
    protected function createLogger($channel, $loggerId, ContainerBuilder $container): void
    {
        if (!in_array($channel, $this->channels)) {
            $logger = new ChildDefinition('logbook.logger_prototype');

            $logger->replaceArgument(0, $channel);
            $container->setDefinition($loggerId, $logger);

            $this->channels[] = $channel;
        }
    }

    /**
     * Creates a copy of a reference and alters the service ID.
     *
     * @param Reference $reference
     * @param string    $serviceId
     *
     * @return Reference
     */
    private function changeReference(Reference $reference, $serviceId): Reference
    {
        return new Reference($serviceId, $reference->getInvalidBehavior());
    }
}
