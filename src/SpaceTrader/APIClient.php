<?php

declare(strict_types=1);

namespace App\SpaceTrader;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class APIClient
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $spaceTraderToken,
        private readonly HttpClientInterface $client,
    ) {
    }

    public function registerAgent(string $symbol, string $faction = 'COSMIC'): array
    {
        $data = [
            'symbol' => $symbol,
            'faction' => $faction,
        ];

        $options = $this->getAccountRequestOptions();
        $options['body'] = json_encode($data);

        $response = $this->client->request('POST', 'https://api.spacetraders.io/v2/register', $options);
        $content = json_decode($response->getContent(), true);

        return $content;
    }

    public function loadAgent(string $name): Agent
    {
        $options = $this->getAgentRequestOptions($name);

        $response = $this->client->request('GET', 'https://api.spacetraders.io/v2/my/agent', $options);
        $content = json_decode($response->getContent(), true);

        return new Agent(...$content['data']);
    }

    public function loadContracts(Agent $agent): array
    {
        $options = $this->getAgentRequestOptions($agent->symbol);

        $response = $this->client->request('GET', 'https://api.spacetraders.io/v2/my/contracts', $options);
        $content = json_decode($response->getContent(), true);

        return $content;
    }

    private function getAccountRequestOptions(): array
    {
        return [
            'auth_bearer' => $this->spaceTraderToken,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }

    private function getAgentRequestOptions(string $name): array
    {
        $DB_LOOKUP = [
            'SP4CE_TR4DER' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZGVudGlmaWVyIjoiU1A0Q0VfVFI0REVSIiwidmVyc2lvbiI6InYyLjMuMCIsInJlc2V0X2RhdGUiOiIyMDI1LTAyLTEzIiwiaWF0IjoxNzM5OTgwNTgxLCJzdWIiOiJhZ2VudC10b2tlbiJ9.qvpdyaWQRP487uHEUzfD3FIVV0ydHsSem4_nrqBG1cq9BtOFr8UXXgVBAO_yX_ZgSsWLtIuoXaTUqE2D3qjb8zhdDT7gm8EGhm2q5Tpsb7Kq4Kcjict8a7OBLY6JMk_NfqOLXF59XIdSstdDH99O_8Qzg9adAJyhqlUeYaweOghGJYmrtkV0ue850qtS43Z98QhVe79EBE1pQKA6BIx-AEFjhhNZ3MUpZ_B11_pgxnYzIf9L8c9pDlaUQcEPp4DC-t52ILHz5HCi4QYtmzvhPaciO6ZxdXhb2JokGegP1xaHs19VBuEAG_mxbTmKPuIS6coAmygK_YHsGBuEXpkyKA',
            'ZER0_SH0T' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZGVudGlmaWVyIjoiWkVSMF9TSDBUIiwidmVyc2lvbiI6InYyLjMuMCIsInJlc2V0X2RhdGUiOiIyMDI1LTAyLTEzIiwiaWF0IjoxNzM5OTgwNTk1LCJzdWIiOiJhZ2VudC10b2tlbiJ9.V-PNoKwlFdfBAQze9Ia3uTerPsIGNzSSZE8yroxNmrKOxzIUkrkSKN-uAFWGgFy4zTJJQDpPswHuN6aLGD4MCzucY1jcAd8UV4OyOY2saLUaCm54ipQA3BgvMi3azZcZO1n9qWVRD8XJ7oGHkTDwhUhgreJpUYoL2mjpJtfawFwQ2RVmp_4zzXkuRFAXNz5DT9kB-1VcDvZ1Xtecj0OX7ahz6QT04RTg3z0jWreNh_ghd2TXWXviS4R422hvIlAOfMxAL9jBfm4iEL5635ooGWDLUn3eQBxo8Tfr1KjN3ACoNMrzU88IwkTVs7ooyA4bSmVZGAZgWRpb7dJPHu29CQ',
        ];

        return [
            'auth_bearer' => $DB_LOOKUP[$name],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }
}
