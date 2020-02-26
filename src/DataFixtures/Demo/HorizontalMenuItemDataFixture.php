<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\HorizontalMenu\HorizontalMenuItemData;
use App\Model\HorizontalMenu\HorizontalMenuItemDataFactory;
use App\Model\HorizontalMenu\HorizontalMenuItemFacade;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

/**
 * @method \App\Model\Category\Category getReference($name)
 */
class HorizontalMenuItemDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemFacade
     */
    private $horizontalMenuItemFacade;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemDataFactory
     */
    private $horizontalMenuItemDataFactory;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemFacade $horizontalMenuItemFacade
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory
     */
    public function __construct(
        HorizontalMenuItemFacade $horizontalMenuItemFacade,
        HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory
    ) {
        $this->horizontalMenuItemFacade = $horizontalMenuItemFacade;
        $this->horizontalMenuItemDataFactory = $horizontalMenuItemDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function load(ObjectManager $manager)
    {
        $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();

        $horizontalMenuItemData->name = 'Stolky';
        $horizontalMenuItemData->url = '/stolky';
        $this->addCategoriesToHorizontalMenuItem($horizontalMenuItemData);
        $this->createItem($horizontalMenuItemData);

        $horizontalMenuItemData->name = 'Židle';
        $horizontalMenuItemData->url = '/zidle';
        $horizontalMenuItemData->categoriesByColumnNumber[1] = [];
        $horizontalMenuItemData->categoriesByColumnNumber[2] = [];
        $horizontalMenuItemData->categoriesByColumnNumber[3] = [];
        $this->createItem($horizontalMenuItemData);

        $horizontalMenuItemData->name = 'Pohovky';
        $horizontalMenuItemData->url = '/pohovky';
        $this->createItem($horizontalMenuItemData);

        $horizontalMenuItemData->name = 'Skříně';
        $horizontalMenuItemData->url = '/skrine';
        $this->createItem($horizontalMenuItemData);

        $horizontalMenuItemData->name = 'Komody';
        $horizontalMenuItemData->url = '/komody';
        $this->createItem($horizontalMenuItemData);
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    private function createItem(HorizontalMenuItemData $horizontalMenuItemData)
    {
        $this->horizontalMenuItemFacade->create($horizontalMenuItemData);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            CategoryDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    private function addCategoriesToHorizontalMenuItem(HorizontalMenuItemData $horizontalMenuItemData)
    {
        $horizontalMenuItemData->categoriesByColumnNumber[1] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_BOOKS),
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_TOYS),
        ];
        $horizontalMenuItemData->categoriesByColumnNumber[2] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS),
        ];
        $horizontalMenuItemData->categoriesByColumnNumber[3] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_FOOD),
        ];
    }

    /**
     * @param string $name
     * @return \App\Model\Category\Category
     */
    private function getCategoryReference(string $name): Category
    {
        return $this->getReference($name);
    }
}
