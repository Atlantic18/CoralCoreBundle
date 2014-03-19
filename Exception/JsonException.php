<?php

namespace Coral\CoreBundle\Exception;

class JsonException extends \Exception
{
    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey()
    {
        return 'An JSON processing exception occurred.';
    }

    /**
     * Message data to be used by the translation component.
     *
     * @return array
     */
    public function getMessageData()
    {
        return array();
    }
}
