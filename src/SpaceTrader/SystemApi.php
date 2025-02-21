<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use App\SpaceTrader\Struct\System;

class SystemApi
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {
    }

    public function loadSystem(string $symbol): System
    {
        $response = $this->apiClient->makeAccountRequest('GET', "https://api.spacetraders.io/v2/systems/$symbol");

        return new System(...$response['data']);
    }
}
