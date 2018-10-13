<?php

namespace Scriptotek\Alma\TaskLists;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\Model;

class ResourceSharingRequest extends Model
{
    /** @var string */
    public $request_id;

    public function __construct(Client $client, $id)
    {
        parent::__construct($client);
        $this->request_id = $id;
    }
}
