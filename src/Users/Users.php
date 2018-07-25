<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Client;
use Scriptotek\Alma\Exception\InvalidQuery;

class Users
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get a User object by id.
     *
     * @param $user_id
     * @return User
     */
    public function get($user_id)
    {
        return User::make($this->client, $user_id);
    }

    /**
     * Iterates over all users matching the given query.
     * Handles continuation.
     * @param string $query
     * @param array $options
     * @return \Generator
     */
    public function search($query, array $options = [])
    {
        // Max number of records to fetch. Set to 0 to fetch all.
        $limit = array_key_exists('limit', $options) ? $options['limit'] : 0;

        // Set to true to do a phrase search
        $phrase = array_key_exists('phrase', $options) ? $options['phrase'] : false;

        // Set to true to expand all query results to full records.
        // Please note that this will make queries significantly slower!
        $expand = array_key_exists('expand', $options) ? $options['expand'] : false;

        // Number of records to fetch each batch. Usually no need to change this.
        $batchSize = array_key_exists('batchSize', $options) ? $options['batchSize'] : 10;

        if ($limit != 0 && $limit < $batchSize) $batchSize = $limit;

        // The API will throw a 400 response if you include properly encoded spaces,
        // but underscores work as a substitute.
        $query = explode(' AND ', $query);
        $query = $phrase ? str_replace(' ', '_', $query) : str_replace(' ', ',', $query);
        $query = implode(' AND ', $query);

        $offset = 0;
        while (true) {
            $response = $this->client->getJSON('/users', ['q' => $query, 'limit' => $batchSize, 'offset' => $offset]);

            // The API sometimes returns total_record_count: -1, with no further error message.
            // Seems to indicate that the query was not understood.
            // See: https://github.com/scriptotek/php-alma-client/issues/8
            if ($response->total_record_count == -1) {
                throw new InvalidQuery($query);
            }

            if ($response->total_record_count == 0) {
                break;
            }

            if (!isset($response->user)) {
                // We cannot trust the value in 'total_record_count', so if there are no more records,
                // we have to assume the result set is depleted.
                // See: https://github.com/scriptotek/php-alma-client/issues/7
                break;
            }

            foreach ($response->user as $data) {

                // Contacts without a primary identifier will have the primary_id
                // field populated with something weird like "no primary id (123456789023)".
                // We ignore those.
                // See: https://github.com/scriptotek/php-alma-client/issues/6
                if (strpos($data->primary_id, 'no primary id') === 0) {
                    continue;
                }
                $user = User::make($this->client, $data->primary_id)
                    ->init($data);
                if ($expand) {
                    $user->init();
                }
                yield $user;
                $offset++;
            }
            if ($offset >= $response->total_record_count) {
                break;
            }
            if ($limit != 0 && $offset >= $limit) {
                break;
            }
        }
    }
}
