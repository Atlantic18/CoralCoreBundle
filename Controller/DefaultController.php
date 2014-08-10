<?php

namespace Coral\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/v1/version")
     * @Method("GET")
     */
    public function versionAction()
    {
        $version = 'N/A';

        $filename = $this->container->getParameter("coral.version_file");
        if(file_exists($filename) && is_readable($filename))
        {
            $version = file_get_contents($filename);
        }

        return new Response('Version: ' . $version);
    }
}
