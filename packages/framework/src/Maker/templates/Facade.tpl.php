<?= "<?php\n"; ?>

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

    public function create(<?= $entity_name; ?>Data $<?= lcfirst($entity_name); ?>Data): <?= $entity_name; ?>
    {
        $<?= lcfirst($entity_name); ?> = new <?= $entity_name; ?>($<?= lcfirst($entity_name); ?>Data);

        $this->em->persist($<?= lcfirst($entity_name); ?>);
        $this->em->flush();

        return $<?= lcfirst($entity_name); ?>;
    }

    public function edit(int $<?= lcfirst($entity_name); ?>Id, <?= $entity_name; ?>Data $<?= lcfirst($entity_name); ?>Data): <?= $entity_name; ?>
    {
        $<?= lcfirst($entity_name); ?> = $this->getById($<?= lcfirst($entity_name); ?>Id);
        $<?= lcfirst($entity_name); ?>->edit($<?= lcfirst($entity_name); ?>Data);

        $this->em->flush();

        return $<?= lcfirst($entity_name); ?>;
    }

    public function getById(int $<?= lcfirst($entity_name); ?>Id): <?= $entity_name; ?>
    {
        return $this-><?= lcfirst($entity_name); ?>Repository->getById($<?= lcfirst($entity_name); ?>Id);
    }

    public function findById(int $<?= lcfirst($entity_name); ?>Id): ?<?= $entity_name; ?>
    {
        return $this-><?= lcfirst($entity_name); ?>Repository->findById($<?= lcfirst($entity_name); ?>Id);
    }

    public function delete(int $<?= lcfirst($entity_name); ?>Id): void
    {
        $<?= lcfirst($entity_name); ?> = $this->getById($<?= lcfirst($entity_name); ?>Id);

        $this->em->remove($<?= lcfirst($entity_name); ?>);
        $this->em->flush();
    }
}
