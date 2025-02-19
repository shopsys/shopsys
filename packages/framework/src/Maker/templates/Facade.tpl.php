<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ $entity_config ?>

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

    public function create(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): <?= $entity_config->entityName; ?>
    {
        $<?= lcfirst($entity_config->entityName); ?> = new <?= $entity_config->entityName; ?>($<?= lcfirst($entity_config->entityName); ?>Data);

        $this->em->persist($<?= lcfirst($entity_config->entityName); ?>);
        $this->em->flush();

        return $<?= lcfirst($entity_config->entityName); ?>;
    }

    public function edit(int $<?= lcfirst($entity_config->entityName); ?>Id, <?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): <?= $entity_config->entityName; ?>
    {
        $<?= lcfirst($entity_config->entityName); ?> = $this->getById($<?= lcfirst($entity_config->entityName); ?>Id);
        $<?= lcfirst($entity_config->entityName); ?>->edit($<?= lcfirst($entity_config->entityName); ?>Data);

        $this->em->flush();

        return $<?= lcfirst($entity_config->entityName); ?>;
    }

    public function getById(int $<?= lcfirst($entity_config->entityName); ?>Id): <?= $entity_config->entityName; ?>
    {
        return $this-><?= lcfirst($entity_config->entityName); ?>Repository->getById($<?= lcfirst($entity_config->entityName); ?>Id);
    }

    public function findById(int $<?= lcfirst($entity_config->entityName); ?>Id): ?<?= $entity_config->entityName; ?>
    {
        return $this-><?= lcfirst($entity_config->entityName); ?>Repository->findById($<?= lcfirst($entity_config->entityName); ?>Id);
    }

    public function delete(int $<?= lcfirst($entity_config->entityName); ?>Id): void
    {
        $<?= lcfirst($entity_config->entityName); ?> = $this->getById($<?= lcfirst($entity_config->entityName); ?>Id);

        $this->em->remove($<?= lcfirst($entity_config->entityName); ?>);
        $this->em->flush();
    }
}
