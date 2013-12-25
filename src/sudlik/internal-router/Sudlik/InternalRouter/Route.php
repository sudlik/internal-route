<?php

namespace Sudlik\InternalRouter;

use Exception;
use Sudlik\InternalRoute\Exception\RouteInvalidName;
use Sudlik\InternalRoute\Exception\RouteRenderFailure;
use Sudlik\InternalRoute\Exception\RouteRenderUnsafeParameters;
use Sudlik\InternalRouter\Instantable;
use Sudlik\InternalRouter\Tokenizer;
use Sudlik\InternalRouter\Resource;

class Route
{
    const REGEXP_PATTERN_NAME       = '#^[\w-.]+$#';
    const REGEXP_PATTERN_OPTIONAL   = '#(\(.*)?\(([^\(\)]+)\)(.*\))?#';
    const REGEXP_PATTERN_SECURE     = '#(/\.\.?)|(\.?\./)#';
    const REGEXP_PATTERN_SEPARATOR  = '#/+|\\\\+#';
    const REGEXP_PATTERN_VARIABLE   = '#\<([^\<\>]+)\>#';
    const CHAR_LESS_THAN            = '<';

    private $name;
    private $path;
    private $default;
    private $type;

    public function __construct($name = 'default', $path = '', $default = [], $type = null)
    {
        $this->setName($name);
        $this->setPath($path);
        $this->setDefault($default);
        $this->setType($type);
    }

    private function setName($name)
    {
        if ($this->isName($name)) {
            $this->name = $name;
        } else {
            throw new RouteInvalidName($name);
        }
    }

    private function isName($name)
    {
        return is_scalar($name) && preg_match(self::REGEXP_PATTERN_NAME, $name);
    }

    private function setPath($path)
    {
        $this->path = preg_replace(self::REGEXP_PATTERN_SEPARATOR, DIRECTORY_SEPARATOR, $path);
    }

    private function setDefault($default)
    {
        $this->default = $default;
    }

    private function setType($type)
    {
        $this->type = $type;
    }

    private function isSafeParameter($param)
    {
        return !preg_match(self::REGEXP_PATTERN_SECURE, $param);
    }

    private function isRenderedPath($rendered_path)
    {
        return !strstr($rendered_path, self::CHAR_LESS_THAN);
    }
    
    private static function get_helper($match)
    {
        $return = strstr($match[ 2 ], self::CHAR_LESS_THAN) ? null : $match[2];

        if ($match[1]) {
            return preg_replace_callback(
                self::REGEXP_PATTERN_OPTIONAL, ['self', 'get_helper'], $match[1] . $return . $match[3]
            );
        } else {
            return $return;
        }
    }
    
    public function render(array $parameters = [])
    {
        foreach ($parameters as $param) {
            if (!$this->isSafeParameter($param)) {
                throw new RouteRenderUnsafeParameters;
                return;
            }
        }

        $rendered_path = (
                new Tokenizer(
                    preg_replace_callback(
                        self::REGEXP_PATTERN_OPTIONAL,
                        array('self', 'get_helper'),
                        preg_replace_callback(
                            self::REGEXP_PATTERN_VARIABLE,
                            function ($match) use ($parameters) {
                                if (isset($parameters[$match[1]])) {
                                    $string = $parameters[$match[1]];
                                } elseif (isset($this->default[$match[1]])) {
                                    $string = $this->default[$match[1]];
                                } else {
                                    return $match[0];
                                }

                                return (new Tokenizer($string))->toTokenized()->getString();
                            },
                            $this->path
                        )
                    )
                )
            )
            ->toTokenized()
            ->getString();

        if ($this->isRenderedPath($rendered_path)) {
            return new Resource($rendered_path);
        } else {
            throw new RouteRenderFailure;
        }
    }
}