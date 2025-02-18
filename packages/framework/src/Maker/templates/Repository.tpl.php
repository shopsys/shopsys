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

    protected function get<?= $entity_name; ?>Repository(): EntityRepository
    {
        return $this->entityManagerInterface->getRepository(<?= $entity_name; ?>::class);
    }

    public function getById(int $<?= lcfirst($entity_name); ?>Id): <?= $entity_name; ?>
    {
        $<?= lcfirst($entity_name); ?> = $this->findById($<?= lcfirst($entity_name); ?>Id);

        if ($<?= lcfirst($entity_name); ?> === null) {
            throw new <?= $entity_name; ?>NotFoundException('<?= $entity_name; ?> with ID "' . $<?= lcfirst($entity_name); ?>Id . '" not found.');
        }

        return $<?= lcfirst($entity_name); ?>;
    }

    public function findById(int $<?= lcfirst($entity_name); ?>Id): ?<?= $entity_name; ?>
    {
        return $this->get<?= $entity_name; ?>Repository()->find($<?= lcfirst($entity_name); ?>Id);
    }
}
