<?php

namespace Scriptotek\Alma;

interface GhostResource {
    public function init($data = null);
    public function getData();
}