<?php

namespace Coral\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Exception\JsonException;
use Coral\CoreBundle\Exception\AuthenticationException;

/**
 * @Route("/v1/observer")
 */
class ObserverController extends JsonController
{
    private function getEventByName($eventName)
    {
        $event = $this->getDoctrine()
            ->getRepository('CoralCoreBundle:Event')
            ->findOneByName($eventName);

        $this->throwNotFoundExceptionIf(!$event, 'No event found for name ' . $eventName);

        return $event;
    }

    /**
     * @Route("/add")
     * @Method("POST")
     */
    public function addAction()
    {
        $request = new JsonParser($this->get("request")->getContent(), true);

        $observer = new \Coral\CoreBundle\Entity\Observer;
        $observer->setEvent($this->getEventByName($request->getMandatoryParam('event')));
        $observer->setAccount($this->getAccount());
        $observer->setUri($request->getMandatoryParam('url'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($observer);
        $em->flush();

        return $this->createCreatedJsonResponse($observer->getId());
    }

    /**
     * @Route("/update/{id}")
     * @Method("POST")
     */
    public function updateAction($id)
    {
        $request = new JsonParser($this->get("request")->getContent(), true);

        $observer = $this->getDoctrine()
            ->getRepository('CoralCoreBundle:Observer')
            ->find($id);

        $this->throwNotFoundExceptionIf(!$observer, 'No observer found for id ' . $id);
        $this->throwExceptionUnlessEntityForAccount($observer);

        $observer->setEvent($this->getEventByName($request->getMandatoryParam('event')));
        $observer->setUri($request->getMandatoryParam('url'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->createSuccessJsonResponse();
    }

    /**
     * @Route("/delete/{id}")
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $observer = $this->getDoctrine()
            ->getRepository('CoralCoreBundle:Observer')
            ->find($id);

        $this->throwNotFoundExceptionIf(!$observer, 'No observer found for id ' . $id);
        $this->throwExceptionUnlessEntityForAccount($observer);

        $em = $this->getDoctrine()->getManager();
        $em->remove($observer);
        $em->flush();

        return $this->createSuccessJsonResponse();
    }

    /**
     * @Route("/list")
     * @Method("GET")
     */
    public function listAction()
    {
        $items = array();
        foreach ($this->getAccount()->getObservers() as $observer) {
            $items[] = array(
                'id'    => $observer->getId(),
                'event' => $observer->getEvent()->getName(),
                'url'   => $observer->getUri()
            );
        }

        return $this->createListJsonResponse($items);
    }

    /**
     * @Route("/list/{eventName}")
     * @Method("GET")
     */
    public function listByEventNameAction($eventName)
    {
        $observers = $this->getDoctrine()->getManager()->createQuery(
                'SELECT o
                FROM CoralCoreBundle:Observer o
                INNER JOIN o.event e WITH (e.name = :event_name)
                INNER JOIN o.account a WITH (a.id = :account_id)'
            )
            ->setParameter('event_name', $eventName)
            ->setParameter('account_id', $this->getAccount()->getId())
            ->getResult();

        $items = array();
        foreach ($observers as $observer) {
            $items[] = array(
                'id'    => $observer->getId(),
                'event' => $eventName,
                'url'   => $observer->getUri()
            );
        }

        return $this->createListJsonResponse($items);
    }
}
