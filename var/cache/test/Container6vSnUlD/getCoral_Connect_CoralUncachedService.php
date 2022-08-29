<?php

namespace Container6vSnUlD;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getCoral_Connect_CoralUncachedService extends Coral_CoreBundle_Tests_Resources_app_AppKernelTestDebugContainer
{
    /**
     * Gets the private 'coral.connect.coral_uncached' shared service.
     *
     * @return \Coral\CoreBundle\Service\Connector\CoralConnector
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/Service/Connector/ConnectorInterface.php';
        include_once \dirname(__DIR__, 4).'/Service/Connector/AbstractConnector.php';
        include_once \dirname(__DIR__, 4).'/Service/Connector/CoralConnector.php';
        include_once \dirname(__DIR__, 4).'/Service/Request/Request.php';
        include_once \dirname(__DIR__, 4).'/Tests/Service/Request/RequestMockup.php';

        return $container->privates['coral.connect.coral_uncached'] = new \Coral\CoreBundle\Service\Connector\CoralConnector(($container->privates['coral.connector.request_uncached'] ?? ($container->privates['coral.connector.request_uncached'] = new \Coral\CoreBundle\Tests\Service\Request\RequestMockup())), (\dirname(__DIR__, 4).'/Tests/Resources/app/fixtures/coral_connect'), 'account', 'apisecretkey');
    }
}
