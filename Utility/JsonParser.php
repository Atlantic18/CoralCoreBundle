<?php

namespace Coral\CoreBundle\Utility;

use Coral\CoreBundle\Exception\JsonException;

class JsonParser
{
    private $params;

    public function __construct($content = null, $isMandatory = false)
    {
        $this->params = $this->importString($content, $isMandatory);
    }

    /**
     * Import string json and parse
     *
     * @param  string  $content     string json
     * @param  boolean $isMandatory content is mandatory or can be empty - false
     * @return array                parsed json string
     */
    public function importString($content, $isMandatory = true)
    {
        if(empty($content) && $isMandatory)
        {
            throw new JsonException("Json content mandatory but none found.");
        }

        $this->params = array();
        if (!empty($content))
        {
            $this->params = @json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new JsonException('Error parsing json content: ' . json_last_error_msg());
            }

            if(null === $this->params)
            {
                // @codeCoverageIgnoreStart
                throw new JsonException("Error parsing json content: '$content'");
                // @codeCoverageIgnoreEnd
            }
        }
        return $this->params;
    }

    /**
     * All parsed params
     *
     * @return array All params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * All parsed params. Needed for cache usage
     *
     * @param array All params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    private function validatePath($path)
    {
        $regexp  = '/^(?:[a-z0-9_\-]*(?:\.|\[\d+\]|\*)?)+$/i';
        $isValid = preg_match($regexp, $path, $matches);
        if(!$isValid)
        {
            throw new JsonException("Unable to parse path '$path'. Doesn't match the regexp: '$regexp'.");
        }
    }

    /**
     * Traverse through params according to path. This is a recursive function.
     *
     * @param  string $path      path to traverse
     * @param  array  $paramsRef reference to the traversed array
     * @return mixed             found value/s
     */
    private function translatePathElement($path, &$paramsRef)
    {
        $dotPos   = strpos($path, '.');
        $currPath = $path;
        if($dotPos !== false)
        {
            $currPath = substr($path, 0, $dotPos);
        }

        if($currPath == '*')
        {
            //Hell begins
            if($dotPos !== false)
            {
                //create a new copy and merge everything from one level up
                $newParamsRef = array();
                foreach ($paramsRef as $subArray) {
                    if(is_array($subArray))
                    {
                        foreach ($subArray as $key => $value) {
                            if(isset($newParamsRef[$key]))
                            {
                                $newParamsRef[$key][] = $value;
                            }
                            else
                            {
                                if(is_array($value))
                                {
                                    $newParamsRef[$key] = $value;
                                }
                                else
                                {
                                    $newParamsRef[$key] = array($value);
                                }
                            }
                        }
                    }
                }
                return $this->translatePathElement(substr($path, $dotPos + 1), $newParamsRef);
            }
            else
            {
                return $paramsRef;
            }
        }
        if(($lbracktePos = strpos($currPath, '[')) !== false)
        {
            $subPath = substr($currPath, 0, $lbracktePos);
            $index   = intval(substr($currPath, $lbracktePos+1, -1));

            if(isset($paramsRef[$subPath]) && is_array($paramsRef[$subPath]) && isset($paramsRef[$subPath][$index]))
            {
                if($dotPos === false)
                {
                    return $paramsRef[$subPath][$index];
                }
                else
                {
                    return $this->translatePathElement(substr($path, $dotPos + 1), $paramsRef[$subPath][$index]);
                }
            }
        }
        else
        {
            if(isset($paramsRef[$currPath]))
            {
                if($dotPos === false)
                {
                    return $paramsRef[$currPath];
                }
                else
                {
                    return $this->translatePathElement(substr($path, $dotPos + 1), $paramsRef[$currPath]);
                }
            }
        }

        return null;
    }

    /**
     * Returns json key value or throws exception if key doesn't exist
     *
     * @throws JsonException If key doesn't exist
     * @param  string $path key
     * @return mixed        value
     */
    public function getMandatoryParam($path)
    {
        $this->validatePath($path);

        $value = $this->translatePathElement($path, $this->params);

        if(null === $value)
        {
            throw new JsonException("Json mandatory param '$path' not found found.");
        }
        return $value;
    }

    /**
     * Returns json true if path exists
     *
     * @param  string $path json path
     * @return boolean      true if path exists
     */
    public function hasParam($path)
    {
        $this->validatePath($path);

        $value = $this->translatePathElement($path, $this->params);

        return (null === $value) ? false : true;
    }

    /**
     * Returns json key value or false if key doesn't exist
     *
     * @param  string $path json key
     * @param  string $default default value to be returned
     * @return mixed        value
     */
    public function getOptionalParam($path, $default = false)
    {
        $this->validatePath($path);

        $value = $this->translatePathElement($path, $this->params);

        if(null === $value)
        {
            return (null === $default) ? null : $default;
        }

        return $value;
    }
}
