<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSets(array(
            'default',
        ));

        $this->addBundles(array(
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Coral\CoreBundle\CoralCoreBundle(),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
        $loader->import(__DIR__.'/test_config.yml');
    }
}