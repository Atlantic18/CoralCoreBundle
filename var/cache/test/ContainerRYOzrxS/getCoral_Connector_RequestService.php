<?php

namespace ContainerRYOzrxS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getCoral_Connector_RequestService extends Coral_CoreBundle_Tests_Resources_app_AppKernelTestDebugContainer
{
    /**
     * Gets the public 'coral.connector.request' shared service.
     *
     * @return \Coral\CoreBundle\Tests\Service\Request\RequestMockup
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/Service/Request/Request.php';
        include_once \dirname(__DIR__, 4).'/Tests/Service/Request/RequestMockup.php';

        return $container->services['coral.connector.request'] = new \Coral\CoreBundle\Tests\Service\Request\RequestMockup();
    }
}
