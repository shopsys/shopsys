<?php

namespace Shopsys\ShopBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use ShopSys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Shopsys\ShopBundle\Component\Migration\MultidomainMigrationTrait;

class Version20180216091409 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $gdprContent = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem.
         Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. 
         Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. 
         Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.
         \nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. 
         Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a... ';

        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
               (\'gdprContentText\', :domainId, :gdprContent, \'string\');
           ', ['domainId' => $domainId, 'gdprContent' => $gdprContent]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
