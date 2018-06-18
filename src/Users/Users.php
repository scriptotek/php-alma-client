<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\ResourceList;

class Users extends ResourceList
{
    protected $resourceName = User::class;

    /**
     * Iterates over all users matching the given query.
     * Handles continuation.
     */
    public function search($query, $full = false, $batchSize = 10)
    {

        // The API will throw a 400 response if you include properly encoded spaces,
        // but underscores work as a substitute.
        $query = str_replace(' ', '_', $query);

        $offset = 0;
        while (true) {
            $response = $this->client->getJSON('/users', ['q' => $query, 'limit' => $batchSize, 'offset' => $offset]);

            if ($response->total_record_count == 0) {
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

                $user = User::fromResponse($this->client, $data);
                if ($full) {
                    $user->fetch();
                }
                yield $user;
                $offset++;
            }
            if ($offset >= $response->total_record_count) {
                break;
            }
        }
    }
}
