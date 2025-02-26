<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?> extends Exception
{
    public function __construct(int $<?= lcfirst($entity_config->entityName); ?>Id, int $domainId, ?Exception $previous = null)
    {
        $message = sprintf('<?= $entity_config->entityName; ?>Domain for entity <?= $entity_config->entityName; ?> with ID %d and domain ID %d not found.', $<?= lcfirst($entity_config->entityName); ?>Id, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
