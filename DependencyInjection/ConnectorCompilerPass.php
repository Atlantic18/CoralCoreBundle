<?php

namespace Coral\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 * @codeCoverageIgnore
 */
class ConnectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('coral.connector'))
        {
            $definition = $container->getDefinition('coral.connector');

            $taggedServices = $container->findTaggedServiceIds('coral.connector.service');

            foreach ($taggedServices as $id => $tagAttributes)
            {
                foreach ($tagAttributes as $attributes)
                {
                    $definition->addMethodCall(
                        'addConnector',
                        array(new Reference($id), $attributes["service"])
                    );
                }
            }
        }
    }
}