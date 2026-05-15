<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20231102161313 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->migrateWishlistsDataToProductLists();
        $this->migrateComparisonsDataToProductLists();

        $this->sql('DROP TABLE IF EXISTS wishlist_items');
        $this->sql('DROP TABLE IF EXISTS wishlists');
        $this->sql('DROP TABLE IF EXISTS compared_items');
        $this->sql('DROP TABLE IF EXISTS comparisons');

        $this->isAppMigrationNotInstalledRemoveIfExists('Version20221107130750');
        $this->isAppMigrationNotInstalledRemoveIfExists('Version20230217093923');
        $this->isAppMigrationNotInstalledRemoveIfExists('Version20230607065227');
    }

    private function migrateWishlistsDataToProductLists(): void
    {
        if (!$this->tableExists('wishlists') || !$this->tableExists('wishlist_items')) {
            return;
        }

        $wishlists = $this->sqlQuery('SELECT * FROM wishlists')->fetchAllAssociative();

        foreach ($wishlists as $wishlist) {
            $wishlistUuid = $wishlist['uuid'] ?? Uuid::uuid4()->toString();
            $this->sql('INSERT INTO product_lists (uuid, customer_user_id, created_at, updated_at, type) VALUES (:uuid, :customer_user_id, :created_at, :updated_at, :type)', [
                'uuid' => $wishlistUuid,
                'customer_user_id' => $wishlist['customer_user_id'],
                'created_at' => $wishlist['updated_at'],
                'updated_at' => $wishlist['updated_at'],
                'type' => 'wishlist',
            ]);
            $productListId = $this->sqlQuery('SELECT id FROM product_lists WHERE uuid = :uuid', ['uuid' => $wishlistUuid])->fetchOne();
            $wishlistItems = $this->sqlQuery('SELECT * FROM wishlist_items WHERE wishlist_id = :wishlist_id', ['wishlist_id' => $wishlist['id']])->fetchAllAssociative();

            foreach ($wishlistItems as $wishlistItem) {
                $this->sql('INSERT INTO product_list_items (uuid, product_list_id, product_id, created_at) VALUES (:uuid, :product_list_id, :product_id, :created_at)', [
                    'uuid' => Uuid::uuid4()->toString(),
                    'product_list_id' => $productListId,
                    'product_id' => $wishlistItem['product_id'],
                    'created_at' => $wishlistItem['created_at'],
                ]);
            }
        }
    }

    private function migrateComparisonsDataToProductLists(): void
    {
        if (!$this->tableExists('comparisons') || !$this->tableExists('compared_items')) {
            return;
        }

        $comparisons = $this->sqlQuery('SELECT * FROM comparisons')->fetchAllAssociative();

        foreach ($comparisons as $comparison) {
            $comparisonUuid = $comparison['uuid'] ?? Uuid::uuid4()->toString();
            $this->sql('INSERT INTO product_lists (uuid, customer_user_id, created_at, updated_at, type) VALUES (:uuid, :customer_user_id, :created_at, :updated_at, :type)', [
                'uuid' => $comparisonUuid,
                'customer_user_id' => $comparison['customer_user_id'],
                'created_at' => $comparison['updated_at'],
                'updated_at' => $comparison['updated_at'],
                'type' => 'comparison',
            ]);
            $comparisonId = $this->sqlQuery('SELECT id FROM product_lists WHERE uuid = :uuid', ['uuid' => $comparisonUuid])->fetchOne();
            $comparisonItems = $this->sqlQuery('SELECT * FROM compared_items WHERE comparison_id = :comparison_id', ['comparison_id' => $comparison['id']])->fetchAllAssociative();

            foreach ($comparisonItems as $comparisonItem) {
                $this->sql('INSERT INTO product_list_items (uuid, product_list_id, product_id, created_at) VALUES (:uuid, :product_list_id, :product_id, :created_at)', [
                    'uuid' => Uuid::uuid4()->toString(),
                    'product_list_id' => $comparisonId,
                    'product_id' => $comparisonItem['product_id'],
                    'created_at' => $comparisonItem['created_at'],
                ]);
            }
        }
    }
}
