<?php

require 'InternalRoute.php';

InternalRoute::set('test', '<name>.<ext>', null, ['name' => 'InternalRoute', 'ext' => 'php']);

$InternalRoute = InternalRoute::get('test');

var_dump('readable', $InternalRoute->readable());
var_dump('writable', $InternalRoute->writable());
var_dump('path', $InternalRoute->path());
var_dump('type', $InternalRoute->type());
var_dump('modified', $InternalRoute->modified());
var_dump('file', $InternalRoute->file());
var_dump('dir', $InternalRoute->dir());