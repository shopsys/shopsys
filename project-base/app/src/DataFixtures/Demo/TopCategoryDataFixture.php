<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;

class TopCategoryDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade $topCategoryFacade
     */
    public function __construct(
        private readonly TopCategoryFacade $topCategoryFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $categories = [
            $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_TOYS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_COFFEE, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_FOOD, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_PHOTO, Category::class),
        ];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $this->topCategoryFacade->saveTopCategoriesForDomain($domainId, $categories);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
        ];
    }
}
