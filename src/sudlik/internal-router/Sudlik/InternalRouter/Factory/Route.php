<?php

namespace Sudlik\InternalRouter\Factory;

class Route
{
    public function getInstance($name, $path, $default, $type)
    {
        return new Route($name, $path, $default, $type);
    }
}