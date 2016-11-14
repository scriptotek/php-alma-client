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
        $offset = 0;
        while (true) {
            $response = $this->client->getJSON('/users', ['q' => $query, 'limit' => $batchSize, 'offset' => $offset]);

            foreach ($response->user as $data) {
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
