<?php

namespace Scriptotek\Alma\TaskLists;

use Scriptotek\Alma\Bibs\Bib;
use Scriptotek\Alma\Client;
use Scriptotek\Alma\Conf\Library;
use Scriptotek\Alma\Model\Model;

class RequestedResource extends Model
{
    public $library;
    public $circ_desk;
    public $bib;

    /**
     * RequestedResource constructor.
     *
     * @param Client  $client
     * @param Library $library
     * @param $circ_desc
     * @param Bib $bib
     */
    public function __construct(Client $client, Library $library, $circ_desc, Bib $bib)
    {
        parent::__construct($client);
        $this->library = $library;
        $this->circ_desk = $circ_desc;
        $this->bib = $bib;
    }
}
