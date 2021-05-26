<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\LazyResource;

/**
 * A single CodeTable resource.
 */
class CodeTable extends LazyResource
{
    /** @var string */
    public $code;

    /**
     * CodeTable constructor.
     *
     * @param Client $client
     * @param string $code
     */
    public function __construct(Client $client, $code)
    {
        parent::__construct($client);
        $this->code = $code;
    }

    /**
    * Return a list of rows referring to the code of the rows in the table.
    *
    * @param string $code - The code of the row in the Table we want to pull.
    *
    * @return array $found - The rows in the code table that match the code passed in.
    */
    public function getRowByCode($code)
    {
        $found = array();
        $codeTable = json_decode($this->client->get($this->urlBase()));
        foreach ($codeTable->row as $row) {
            if ($row->code == $code) {
                array_push($found,$row);
            }
        }
        return($found);
    }

    /**
     * Check if we have the full representation of our data object.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    protected function isInitialized($data)
    {
        return isset($data->name);
    }

    /**
     * Generate the base URL for this resource.
     *
     * @return string
     */
    protected function urlBase()
    {
        return "/conf/code-tables/{$this->code}";
    }

}
