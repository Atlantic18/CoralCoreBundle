<?php

namespace Coral\CoreBundle\Tests\DependencyInjection;

use Coral\CoreBundle\DependencyInjection\CoralCoreExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class CoralCoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @dataProvider getFormats
     */
    public function testLoadEmptyConfiguration($format)
    {
        $container = $this->createContainer();
        $container->registerExtension(new CoralCoreExtension());
        $this->loadFromFile($container, 'empty', $format);
        $this->compileContainer($container);
    }

    /**
     * @dataProvider getFormats
     */
    public function testLoadMinConfiguration($format)
    {
        $container = $this->createContainer();
        $container->registerExtension(new CoralCoreExtension());
        $this->loadFromFile($container, 'min', $format);
        $this->compileContainer($container);

        $this->assertEquals('https://example.com', $container->getParameter('coral.connect.uri'));
        $this->assertEquals('account', $container->getParameter('coral.connect.account'));
        $this->assertEquals('apisecretkey', $container->getParameter('coral.connect.api_key'));
    }

    /**
     * @dataProvider getFormats
     */
    public function testLoadFullConfiguration($format)
    {
        $container = $this->createContainer();
        $container->registerExtension(new CoralCoreExtension());
        $this->loadFromFile($container, 'full', $format);
        $this->compileContainer($container);

        $this->assertEquals('https://example.com', $container->getParameter('coral.connect.uri'));
        $this->assertEquals('account', $container->getParameter('coral.connect.account'));
        $this->assertEquals('apisecretkey', $container->getParameter('coral.connect.api_key'));
    }

    public function getFormats()
    {
        return array(
            array('yml')
        );
    }

    private function createContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => __DIR__,
            'kernel.root_dir'  => __DIR__.'/Fixtures',
            'kernel.charset'   => 'UTF-8',
            'kernel.debug'     => false,
            'kernel.bundles'   => array('CoralCoreBundle' => 'Coral\\CoreBundle\\CoralCoreBundle'),
        )));

        return $container;
    }

    private function compileContainer(ContainerBuilder $container)
    {
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
    }

    private function loadFromFile(ContainerBuilder $container, $file, $format)
    {
        $locator = new FileLocator(__DIR__.'/Fixtures/'.$format);

        switch ($format) {
            case 'php':
                $loader = new PhpFileLoader($container, $locator);
                break;
            case 'xml':
                $loader = new XmlFileLoader($container, $locator);
                break;
            case 'yml':
                $loader = new YamlFileLoader($container, $locator);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported format: %s', $format));
        }

        $loader->load($file.'.'.$format);
    }
}