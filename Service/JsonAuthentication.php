<?php

namespace Coral\CoreBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Coral\CoreBundle\Controller\JsonController;
use Coral\CoreBundle\Exception\JsonException;
use Coral\CoreBundle\Exception\AuthenticationException;

class JsonAuthentication implements EventSubscriberInterface
{
    private $request;

    public function onKernelController(FilterControllerEvent $event)
    {
        $controllerArray = $event->getController();
        $this->request   = $event->getRequest();

        if((count($controllerArray) != 2) || !($controllerArray[0] instanceof JsonController))
        {
            //throw new InvalidTypeException("Controller not instanceof controller: " . get_class($controllerArray[0]));
            return true;
        }
        $controller = $controllerArray[0];

        if(!$this->request->headers->has('X-CORAL-ACCOUNT'))
        {
            throw new AuthenticationException("Missing header X-CORAL-ACCOUNT");
        }
        $accountName = $this->request->headers->get('X-CORAL-ACCOUNT');

        if(!$this->request->headers->has('X-CORAL-SIGN'))
        {
            throw new AuthenticationException("Missing header X-CORAL-SIGN");
        }
        $signature = $this->request->headers->get('X-CORAL-SIGN');

        if(!$this->request->headers->has('X-CORAL-DTIME'))
        {
            throw new AuthenticationException("Missing header X-CORAL-DTIME");
        }
        $dtime = $this->request->headers->get('X-CORAL-DTIME');

        if(abs(time() - $dtime) > 300)
        {
            throw new AuthenticationException('Time difference (X-CORAL-DTIME) in the timestamp is more than 5minutes.');
        }

        $account = $controller->getDoctrine()
            ->getRepository('CoralCoreBundle:Account')
            ->findOneByName($accountName);

        if (!$account) {
            throw new AuthenticationException('No account found for name [' . $accountName . '].');
        }

        foreach ($account->getRemoteApplications() as $client) {
            if($this->isValidSignature($client->getToken()))
            {
                $controller->setAccount($account);

                return true;
            }
        }

        throw new AuthenticationException('Invalid signature.');
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    private function isValidSignature($privateKey)
    {
        $signature   = $this->request->headers->get('X-CORAL-SIGN');
        $dtime       = $this->request->headers->get('X-CORAL-DTIME');
        $uri         = $this->request->getUri();
        $bodyContent = $this->request->getContent();
        $mySignature = hash('sha256', $privateKey . '|' . $dtime . '|' . $uri . ($bodyContent ? '|' . $bodyContent : ''));

        return ($signature == $mySignature);
    }
}
