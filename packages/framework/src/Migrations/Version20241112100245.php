<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Override;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241112100245 extends AbstractMigration implements DomainAwareInterface, EntityManagerAwareInterface
{
    use MultidomainMigrationTrait;

    protected ?EntityManagerInterface $entityManager = null;

    #[Override]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $rootBlogCategories = $this->sql('SELECT id FROM blog_categories WHERE parent_id IS NULL AND level = 0')->fetchAllAssociative();

        if (count($rootBlogCategories) === 1) {
            $blogCategoryId = reset($rootBlogCategories)['id'];
            $translation = $this->sql('SELECT 1 FROM blog_category_translations WHERE translatable_id = ?', [$blogCategoryId])->fetchOne();

            if ($translation === false) {
                $this->sql('UPDATE blog_categories SET parent_id = NULL WHERE parent_id = ?', [$blogCategoryId]);
                $this->sql('DELETE FROM blog_category_domains WHERE blog_category_id = ?', [$blogCategoryId]);
                $this->sql('DELETE FROM blog_categories WHERE id = ?', [$blogCategoryId]);
                $this->sql('UPDATE blog_categories SET level = level -1');
            }

            $repository = $this->entityManager->getRepository(BlogCategory::class);

            if ($repository instanceof NestedTreeRepository) {
                $repository->recover();
            }
        } elseif (count($rootBlogCategories) > 1) {
            throw new Exception('There is more than one root blog category. Please see upgrade notes.');
        }
    }
}
