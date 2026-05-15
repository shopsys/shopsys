<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20251128112222 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('COMMENT ON COLUMN promo_codes.datetime_valid_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN promo_codes.datetime_valid_to IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN administrator_activities.login_time IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN administrator_activities.last_action_time IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN transfer_issues.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN transfer_issues.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN notification_bars.validity_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN notification_bars.validity_to IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN adverts.datetime_visible_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN adverts.datetime_visible_to IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN blog_articles.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN blog_articles.publish_date IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN complaints.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN personal_data_access_request.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN watchdogs.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN watchdogs.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN watchdogs.valid_until IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN orders.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN orders.order_payment_status_page_valid_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN administrators.roles_changed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('
            COMMENT ON COLUMN administrators.transfer_issues_last_seen_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN administrators.reset_password_hash_valid_through IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN cart_items.added_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN carts.modified_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN products.selling_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN products.selling_to IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN customer_user_refresh_token_chain.expired_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN customer_users.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN customer_users.reset_password_hash_valid_through IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN customer_users.last_security_change IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN articles.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN slider_items.datetime_visible_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN slider_items.datetime_visible_to IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN gift_plans.valid_from IS \'(DC2Type:datetime_immutable)\';');
        $this->sql('COMMENT ON COLUMN gift_plans.valid_to IS \'(DC2Type:datetime_immutable)\';');
        $this->sql('COMMENT ON COLUMN customer_user_login_types.last_logged_in_at IS \'(DC2Type:datetime_immutable)\';');
        $this->sql('COMMENT ON COLUMN uploaded_files.modified_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN customer_uploaded_files.modified_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN images.modified_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN entity_log.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN friendly_urls.last_modification IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN cron_modules.last_started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN cron_modules.last_finished_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN cron_module_runs.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN cron_module_runs.finished_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN login_as_user_exchange_tokens.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN orders.delivered_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
