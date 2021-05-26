<?php

namespace Scriptotek\Alma\Conf;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Model\ReadOnlyArrayAccess;

/**
 * A non-iterable collection of CodeTable resources
 */
class CodeTables implements \ArrayAccess
{
    use ReadOnlyArrayAccess;

    protected $client;

    /**
     * CodeTables constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get a CodeTable by identifier
     *
     * @param $code The identifier of a CodeTable
     *
     * @return CodeTable
     */
    public function get($code)
    {
        return CodeTable::make($this->client, $code);
    }

    /**
    * Return a object containing a list of code tables.
    *
    * @return CodeTable ojbect list.
    */
    public function getCodeTables()
    {
        return json_decode($this->client->get($this->urlBase()));
    }
    
    /**
    * Generate the base URL for this resource.
    *
    * @return string
    */
    protected function urlBase()
    {
        return '/conf/code-tables';
    }
    
}
