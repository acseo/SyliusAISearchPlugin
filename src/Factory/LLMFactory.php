<?php

namespace ACSEO\SyliusAISearchPlugin\Factory;

use OpenAI;
use OpenAI\Client;

class LLMFactory
{
    public static function create(?string $apiKey, ?string $organization, ?string $baseUri): Client
    {
        $factory = OpenAI::factory();

        foreach ([
            $apiKey ? fn($f) => $f->withApiKey($apiKey) : null,
            $organization ? fn($f) => $f->withOrganization($organization) : null,
            $baseUri ? fn($f) => $f->withBaseUri($baseUri) : null,
        ] as $modifier) {
            if ($modifier) {
                $factory = $modifier($factory);
            }
        }

        return $factory->make();
    }
}
