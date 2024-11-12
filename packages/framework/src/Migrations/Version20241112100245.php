<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20241112100245 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
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

            $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
            $repository = $entityManager->getRepository(BlogCategory::class);

            if ($repository instanceof NestedTreeRepository) {
                $repository->recover();
            }
        } elseif (count($rootBlogCategories) > 1) {
            throw new Exception('There is more than one root blog category. Please see upgrade notes.');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
