<?php

namespace ACSEO\SyliusAISearchPlugin\Twig\Components;

use ACSEO\SyliusAISearchPlugin\Manager\ProductQueryParser;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $aiQuery = null;

    public ?array $products = null;

    #[LiveProp]
    public ?string $errorMessage = null;

    public function __construct(
        private readonly ProductQueryParser $queryToFormInputService,
    )
    {
    }

    #[LiveAction]
    public function processAiQuery(): void
    {
        if ($this->aiQuery === null) {
            $this->errorMessage = 'Please provide a query.';
            return;
        }

        $this->errorMessage = null;

        try {
            $this->products = $this->queryToFormInputService->transformUserQueryToFormData($this->aiQuery);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error processing your query: ' . $e->getMessage();
        }
    }
}
