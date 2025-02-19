<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ $entity_config ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?> extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string <?= strtoupper($entity_config->entityName); ?>_REFERENCE = '<?= strtolower($entity_config->entityName); ?>_reference';

    public function __construct(
<?php foreach ($constructor_dependencies as $dependency): ?>
        private readonly <?= $dependency; ?>,<?= PHP_EOL; ?>
<?php endforeach; ?>
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $<?= lcfirst($entity_config->entityName); ?>Data = $this-><?= lcfirst($entity_config->entityName); ?>DataFactory->create();
            $domainId = $domainConfig->getId();

            // $<?= lcfirst($entity_config->entityName); ?>Data-> // set data object properties here

            $this->create<?= $entity_config->entityName; ?>($<?= lcfirst($entity_config->entityName); ?>Data, $domainId, self::<?= strtoupper($entity_config->entityName); ?>_REFERENCE);
        }
    }

    private function create<?= $entity_config->entityName; ?>(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data, int $domainId, ?string $referenceName = null): void
    {
        $<?= lcfirst($entity_config->entityName); ?> = $this-><?= lcfirst($entity_config->entityName); ?>Facade->create($<?= lcfirst($entity_config->entityName); ?>Data);

        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $<?= lcfirst($entity_config->entityName); ?>, $domainId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [];
    }
}
