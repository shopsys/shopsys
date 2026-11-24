<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261124120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        // The "Shippings and payments" page was split into two pages, so its pinned menu item is replaced by both of them.
        $this->sql('
            UPDATE administrator_pinned_menu_items AS following
            SET position = following.position + 1
            FROM administrator_pinned_menu_items AS pinned
            WHERE pinned.route_name = \'admin_transportandpayment_list\'
                AND following.administrator_id = pinned.administrator_id
                AND following.position > pinned.position
        ');
        $this->sql('
            INSERT INTO administrator_pinned_menu_items (administrator_id, route_name, position)
            SELECT administrator_id, \'admin_payment_list\', position + 1
            FROM administrator_pinned_menu_items
            WHERE route_name = \'admin_transportandpayment_list\'
        ');
        $this->sql('
            UPDATE administrator_pinned_menu_items
            SET route_name = \'admin_transport_list\'
            WHERE route_name = \'admin_transportandpayment_list\'
        ');
    }
}
