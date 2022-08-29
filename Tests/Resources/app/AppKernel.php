<?php

namespace Coral\CoreBundle\Tests\Resources\app;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return array(
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Coral\CoreBundle\CoralCoreBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->import(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * Returns the KernelDir of the CHILD class,
     * i.e. the concrete implementation in the bundles
     * src/ directory (or wherever).
     */
    public function getKernelDir()
    {
        $refl = new \ReflectionClass($this);
        $fname = $refl->getFileName();
        $kernelDir = dirname($fname);
        return $kernelDir;
    }
}