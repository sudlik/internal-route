<?php

namespace Sudlik\InternalRouter\Factory;

class Tokenizer
{
    public function getInstance($string)
    {
        return new Tokenizer($string);
    }
}