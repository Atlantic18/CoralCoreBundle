<?php

namespace ContainerRYOzrxS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getCoral_Connector_RequestUncachedService extends Coral_CoreBundle_Tests_Resources_app_AppKernelTestDebugContainer
{
    /**
     * Gets the private 'coral.connector.request_uncached' shared service.
     *
     * @return \Coral\CoreBundle\Tests\Service\Request\RequestMockup
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/Service/Request/Request.php';
        include_once \dirname(__DIR__, 4).'/Tests/Service/Request/RequestMockup.php';

        return $container->privates['coral.connector.request_uncached'] = new \Coral\CoreBundle\Tests\Service\Request\RequestMockup();
    }
}
