<?php

declare(strict_types=1);

namespace Involta\Universe;

trait Methods
{
    public function clientsList(int $offset = 0, int $limit = 1000): array
    {
        return $this->call(
            [
                'method' => 'clients_list',
                'count' => $limit,
                'start_from' => $offset
            ]
        );
    }

    public function getClient(int $id): array
    {
        return $this->call(
            [
                'method' => 'get_client',
                'ClientId' => $id,
            ]
        );
    }
}
