<?php

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();
$collection->addCollection(
    $loader->import(__DIR__.'/test_routing.yml')
);

return $collection;