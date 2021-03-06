<?php

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Doctrine\Common\Cache\CacheProvider;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class CachedBestsellingProductFacade
{
    const LIFETIME = 43200; // 12h

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade
     */
    protected $bestsellingProductFacade;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cacheProvider;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository
     */
    protected $pricingGroupRepository;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade $bestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     */
    public function __construct(
        CacheProvider $cacheProvider,
        BestsellingProductFacade $bestsellingProductFacade,
        ProductRepository $productRepository,
        PricingGroupRepository $pricingGroupRepository
    ) {
        $this->cacheProvider = $cacheProvider;
        $this->bestsellingProductFacade = $bestsellingProductFacade;
        $this->productRepository = $productRepository;
        $this->pricingGroupRepository = $pricingGroupRepository;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedBestsellingProducts($domainId, Category $category, PricingGroup $pricingGroup)
    {
        $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);
        $sortedProductsIds = $this->cacheProvider->fetch($cacheId);

        if ($sortedProductsIds === false) {
            $bestsellingProducts = $this->bestsellingProductFacade->getAllOfferedBestsellingProducts(
                $domainId,
                $category,
                $pricingGroup
            );
            $this->saveToCache($bestsellingProducts, $cacheId);

            return $bestsellingProducts;
        } else {
            return $this->getSortedProducts($domainId, $pricingGroup, $sortedProductsIds);
        }
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function invalidateCacheByDomainIdAndCategory($domainId, Category $category)
    {
        $pricingGroups = $this->pricingGroupRepository->getPricingGroupsByDomainId($domainId);
        foreach ($pricingGroups as $pricingGroup) {
            $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);
            $this->cacheProvider->delete($cacheId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $bestsellingProducts
     * @param string $cacheId
     */
    protected function saveToCache(array $bestsellingProducts, $cacheId)
    {
        $sortedProductIds = [];
        foreach ($bestsellingProducts as $product) {
            $sortedProductIds[] = $product->getId();
        }

        $this->cacheProvider->save($cacheId, $sortedProductIds, self::LIFETIME);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int[] $sortedProductIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getSortedProducts($domainId, PricingGroup $pricingGroup, array $sortedProductIds)
    {
        return $this->productRepository->getOfferedByIds($domainId, $pricingGroup, $sortedProductIds);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return string
     */
    protected function getCacheId($domainId, Category $category, PricingGroup $pricingGroup)
    {
        return $domainId . '_' . $category->getId() . '_' . $pricingGroup->getId();
    }
}
