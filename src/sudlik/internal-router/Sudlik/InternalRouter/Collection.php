<?php

namespace Sudlik\InternalRouter;

use ArrayObject;
use Sudlik\InternalRouter\Route;

class Collection extends ArrayObject
{
    public function __construct(array $routes = [])
    {
        foreach ($routes as $Route) {
            $this->append($Route);
        }
    }

    public function create($name = 'default', $path = '', $default = [], $type = null)
    {
        $Route = new Route($name, $path, $default, $type);

        $this->offsetSet($name, $Route);

        return $Route;
    }
}