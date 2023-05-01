<?php

namespace Solvrtech\Symfony\Logbook;

use Solvrtech\Symfony\Logbook\DependencyInjection\Compiler\LoggerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LogbookBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new LoggerCompilerPass());
    }
}
