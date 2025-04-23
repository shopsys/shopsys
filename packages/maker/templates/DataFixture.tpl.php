<?= "<?php\n"; ?>
<?php /** @var \Shopsys\MakerBundle\EntityConfig\EntityConfig $entity_config */ $entity_config ?>

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
        $<?= lcfirst($entity_config->entityName); ?>Data = $this-><?= lcfirst($entity_config->entityName); ?>DataFactory->create();

        // $<?= lcfirst($entity_config->entityName); ?>Data-> // set common data object properties here

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            // $<?= lcfirst($entity_config->entityName); ?>Data-> // set domain and locale data object properties here
        }

        $this->create<?= $entity_config->entityName; ?>($<?= lcfirst($entity_config->entityName); ?>Data, self::<?= strtoupper($entity_config->entityName); ?>_REFERENCE);
    }

    private function create<?= $entity_config->entityName; ?>(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data, ?string $referenceName = null): void
    {
        $<?= lcfirst($entity_config->entityName); ?> = $this-><?= lcfirst($entity_config->entityName); ?>Facade->create($<?= lcfirst($entity_config->entityName); ?>Data);

        if ($referenceName !== null) {
            $this->addReference($referenceName, $<?= lcfirst($entity_config->entityName); ?>);
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
