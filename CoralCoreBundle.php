<?php

namespace Coral\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Coral\CoreBundle\DependencyInjection\CoralCoreExtension;
use Coral\CoreBundle\DependencyInjection\ConnectorCompilerPass;

class CoralCoreBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new CoralCoreExtension());

        $container->addCompilerPass(new ConnectorCompilerPass);
    }
}
