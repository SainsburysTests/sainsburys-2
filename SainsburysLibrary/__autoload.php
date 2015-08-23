<?php
/**
 * @param string $className
 */
function __autoload($className)
{
    $classFile = str_replace('_', '/', $className) . '.php';
    require_once $classFile;
}