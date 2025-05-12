<?php

namespace ACSEO\SyliusAISearchPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/{_locale}', name: 'sylius_product_')]
class ProductSearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function search(): Response
    {
        return $this->render('search/index.html.twig');
    }
}
