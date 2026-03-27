<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240722150000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $customerUsersData = $this->sqlQuery('SELECT id, last_login, created_at FROM customer_users')->fetchAllAssociative();

        foreach ($customerUsersData as $customerUserData) {
            $lastLogin = $customerUserData['last_login'] ?? $customerUserData['created_at'];
            $customerUserId = $customerUserData['id'];

            $this->sql('
                INSERT INTO customer_user_login_types(customer_user_id, login_type, last_logged_in_at)
                VALUES (:customerUserId, :loginType, :lastLogin)
                ON CONFLICT DO NOTHING
            ', [
                'customerUserId' => $customerUserId,
                'loginType' => 'web',
                'lastLogin' => $lastLogin,
            ]);
        }

        $this->sql('ALTER TABLE customer_users DROP last_login');
    }
}
