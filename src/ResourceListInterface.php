<?php

namespace Scriptotek\Alma;

interface ResourceListInterface
{
    // public $client;
    // protected $resourceName;
    // protected $_resources;

    public function getFactoryArgs($element);

    public function getResources();
    public function getResource($id);

}
