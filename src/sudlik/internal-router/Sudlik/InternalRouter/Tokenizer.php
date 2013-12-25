<?php

namespace Sudlik\InternalRouter;

use Sudlik\InternalRoute\Exception\TokenizerInvalidString;

class Tokenizer
{
    private static $CHAR_TO_TOKEN = [
        '#<#'  => 'INTERNAL_TOKEN_A',
        '#>#'  => 'INTERNAL_TOKEN_B',
        '#\(#' => 'INTERNAL_TOKEN_C',
        '#\)#' => 'INTERNAL_TOKEN_D',
    ];

    private static $TOKEN_TO_CHAR = [
        '#INTERNAL_TOKEN_A#' => '<',
        '#INTERNAL_TOKEN_B#' => '>',
        '#INTERNAL_TOKEN_C#' => '(',
        '#INTERNAL_TOKEN_D#' => ')',
    ];

    private $string;

    public function __construct($string)
    {
        $this->setString($string);
    }

    private function setString($string)
    {
        if ($this->isString($string)) {
            $this->string = $string;
        } else {
            throw new TokenizerInvalidString($string);
        }
    }

    private function isString($string)
    {
        return is_string($string);
    }

    public function getString()
    {
        return $this->string;
    }
    
    public function toTokenized()
    {
        $this->string = preg_replace(array_keys(self::$CHAR_TO_TOKEN), self::$CHAR_TO_TOKEN, $this->string);

        return $this;
    }
    
    public function toOriginal()
    {
        $this->string = preg_replace(array_keys(self::$TOKEN_TO_CHAR), self::$TOKEN_TO_CHAR, $this->string);

        return $this;
    }
}