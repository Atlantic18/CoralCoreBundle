<?php

namespace Coral\CoreBundle\Exception;

class ConnectorException extends \LogicException
{
    /* HTTP Trace object
     *
     * @var HttpTrace
     */
    protected $trace = null;

    public function setHttpTrace(HttpTrace $trace)
    {
        $this->trace = $trace;
    }

    /* Get HTTP Trace object
     *
     * @return HttpTrace error trace
     */
    public function getHttpTrace()
    {
        return $this->trace;
    }
}