<?= "<?php\n"; ?>
<?php /** @var \Shopsys\MakerBundle\EntityConfig\EntityConfig $entity_config */ $entity_config ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?>
{

public function __construct(
<?php foreach ($constructor_dependencies as $dependency): ?>
    private readonly <?= $dependency; ?>,<?= PHP_EOL; ?>
<?php endforeach; ?>
    ) {
    }

    protected function get<?= $entity_config->entityName; ?>Repository(): EntityRepository
    {
        return $this->em->getRepository(<?= $entity_config->entityName; ?>::class);
    }

    public function getById(int $<?= lcfirst($entity_config->entityName); ?>Id): <?= $entity_config->entityName; ?>
    {
        $<?= lcfirst($entity_config->entityName); ?> = $this->findById($<?= lcfirst($entity_config->entityName); ?>Id);

        if ($<?= lcfirst($entity_config->entityName); ?> === null) {
            throw new <?= $entity_config->entityName; ?>NotFoundException('<?= $entity_config->entityName; ?> with ID "' . $<?= lcfirst($entity_config->entityName); ?>Id . '" not found.');
        }

        return $<?= lcfirst($entity_config->entityName); ?>;
    }

    public function findById(int $<?= lcfirst($entity_config->entityName); ?>Id): ?<?= $entity_config->entityName; ?>
    {
        return $this->get<?= $entity_config->entityName; ?>Repository()->find($<?= lcfirst($entity_config->entityName); ?>Id);
    }
}
