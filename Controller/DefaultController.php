<?php

namespace Coral\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends JsonController
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

        return new JsonResponse(array(
            'status'  => 'ok',
            'version' => $version
        ), 200);
    }
}
