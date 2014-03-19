<?php

namespace Coral\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Exception\JsonException;
use Coral\CoreBundle\Exception\AuthenticationException;

abstract class JsonController extends Controller
{
    protected $_account;

    protected function createCreatedJsonResponse($id)
    {
        return new JsonResponse(array(
            'status'  => 'ok',
            'id'      => $id
        ), 201);
    }

    protected function createListJsonResponse($items)
    {
        return new JsonResponse(array(
            'status'  => 'ok',
            'items'   => $items
        ), 200);
    }

    protected function createSuccessJsonResponse($additionalParams = null)
    {
        $response = array('status'  => 'ok');

        if(null === $additionalParams)
        {
            return new JsonResponse($response, 200);
        }

        if(!is_array($additionalParams))
        {
            throw new InvalidTypeException('Only array paremeter is allowed. Passed: ' . $additionalParams);
        }

        return new JsonResponse(array_merge($response, $additionalParams), 200);
    }

    /**
     * Check whether the entity has correct account otherwise throws exception
     *
     * @param  Entity $entity entity
     * @throws AuthenticationException
     */
    protected function throwExceptionUnlessEntityForAccount($entity)
    {
        if($entity->getAccount()->getId() != $this->getAccount()->getId())
        {
            throw new AuthenticationException("Editing invalid observer.");
        }
    }

    protected function throwNotFoundExceptionIf($throwException = false, $message = '')
    {
        if($throwException)
        {
            throw new NotFoundHttpException(($message ? $message : 'Not found error.'));
        }
    }

    public function setAccount(\Coral\CoreBundle\Entity\Account $account)
    {
        $this->_account = $account;
    }

    public function getAccount()
    {
        return $this->_account;
    }
}
