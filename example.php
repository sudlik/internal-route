<?php

require 'vendor/autoload.php';

use Sudlik\InternalRouter\Collection;

$Collection = new Collection;

$Collection->create('class', 'src/sudlik/internal-router/<path>/<name>.<ext>', ['name' => 'Route', 'ext' => 'php']);

$Route = $Collection->offsetGet('class')->render(['path' => 'Sudlik/InternalRouter']);

var_dump('readable', $Route->isReadable());
var_dump('writable', $Route->isWritable());
var_dump('orginal path', $Route->getOriginalPath());
var_dump('type', $Route->getMimeType());
var_dump('modified', $Route->getMTime());
var_dump('file', $Route->isFile());
var_dump('dir', $Route->isDir());