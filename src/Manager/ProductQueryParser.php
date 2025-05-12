<?php

namespace ACSEO\SyliusAISearchPlugin\Manager;

use ACSEO\SyliusAISearchPlugin\Repository\ProductRepository;
use OpenAI\Client;

readonly class ProductQueryParser
{
    public function __construct(
        private Client $openAiClient,
        private ProductRepository $productRepository,
        private string $model = 'gpt-4o-mini',
        private int $maxTokens = 5000
    ) {}

    public function transformUserQueryToFormData(string $userQuery): array
    {
        $cleanUserQuery = preg_replace('/[\x00-\x1F\x7F]/', '', $userQuery);
        $cleanUserQuery = mb_convert_encoding($cleanUserQuery, 'UTF-8', 'auto');

        $systemPrompt = <<<PROMPT
You are an assistant tasked with transforming a user query into structured form data.
Your job is to analyze the provided query and extract the relevant product fields, including:
- product name (e.g., "dress")
- product options like color, size, or material (e.g., {"color": "red", "size": "M"})
- product description (e.g., "elegant")
- product taxon (e.g., "women")

Make sure you detect and separate multiple options if they are present (e.g., color, size).
Use only the explicitly provided information.
Return the result in a structured JSON format with no missing required fields.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $cleanUserQuery],
        ];

        $jsonSchema = [
            'name' => 'advanced_product_form',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'product_name' => [
                        'type' => 'string',
                        'description' => 'product name, e.g.: dress, trousers, shoes.'
                    ],
                    'product_options' => [
                        'type' => 'object',
                        'description' => 'options detected, e.g.: {"color": "red", "size": "M", "material": "cotton"}.',
                        'additionalProperties' => [
                            'type' => 'string'
                        ]
                    ],
                    'product_description' => [
                        'type' => 'string',
                        'description' => 'short description of the product, e.g.: trendy, comfortable, elegant.'
                    ],
                    'product_taxon' => [
                        'type' => 'string',
                        'description' => 'product category, e.g.: women, men, children.'
                    ],
                    'product_attributes' => [
                        'type' => 'object',
                        'description' => 'additional attributes, e.g.: {"season": "summer", "fit": "slim"}.',
                        'additionalProperties' => [
                            'type' => 'string'
                        ]
                    ],
                    'semantic_tags' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ],
                        'description' => 'related semantic keywords, e.g.: ["light clothing", "summer outfit"].'
                    ],
                ],
                'required' => [
                    'product_name',
                    'product_description',
                    'product_taxon',
                    'semantic_tags',
                ],
                'additionalProperties' => false,
            ],
        ];

        $response = $this->openAiClient->chat()->create([
            'model' => $this->model ?: 'gpt-4o-mini',
            'messages' => $messages,
            'temperature' => 0,
            'max_tokens' => $this->maxTokens,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => $jsonSchema
            ],
        ]);

        if (isset($response['choices'][0]['message']['content'])) {
            $rawData = json_decode(
                $response['choices'][0]['message']['content'],
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return $this->productRepository->searchProducts($rawData);
        }

        return [];
    }
}
