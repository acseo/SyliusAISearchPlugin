<?php

namespace ACSEO\SyliusAISearchPlugin\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    public function searchProducts(array $criteria): array
    {
        $locale = 'en_US';
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->innerJoin('o.productTaxons', 'productTaxon')
        ;

        if (isset($criteria['product_name']) && $criteria['product_name'] !== '') {
            $qb
                ->andWhere('translation.name LIKE :name')
                ->setParameter('name', '%' . $criteria['product_name'] . '%')
            ;
        }

        if (isset($criteria['product_description']) && $criteria['product_description'] !== '') {
            $qb
                ->andWhere('translation.description LIKE :description')
                ->setParameter('description', '%' . $criteria['product_description'] . '%')
            ;
        }

        if (isset($criteria['product_taxon']) && $criteria['product_taxon'] !== '') {
            $qb
                ->innerJoin('productTaxon.taxon', 'taxon')
                ->innerJoin('taxon.translations', 'taxon_translation', 'WITH', 'translation.locale = :locale')
                ->andWhere('taxon_translation.name LIKE :taxonName')
                ->setParameter('taxonName', '%' . $criteria['product_taxon'] . '%')
            ;
        }

//        if (!empty($criteria['product_color'])) {
//            $qb->andWhere('o.productColor = :color')
//                ->setParameter('color', $criteria['product_color']);
//        }
//
//        if (!empty($criteria['product_attributes'])) {
//            foreach ($criteria['product_attributes'] as $attributeKey => $attributeValue) {
//                $qb->andWhere("JSON_EXTRACT(o.attributes, '$.\"$attributeKey\"') = :$attributeKey")
//                    ->setParameter($attributeKey, $attributeValue);
//            }
//        }

        $qb
            ->setParameter('locale', $locale)
            ->setMaxResults(8)
        ;

        return $qb->getQuery()->getResult();
    }
}
