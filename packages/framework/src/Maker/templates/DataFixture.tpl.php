<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?> extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string <?= strtoupper($entity_name); ?>_REFERENCE = '<?= strtolower($entity_name); ?>_reference';

    public function __construct(
<?php foreach ($constructor_dependencies as $dependency): ?>
        private readonly <?= $dependency; ?>,<?= PHP_EOL; ?>
<?php endforeach; ?>
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $<?= lcfirst($entity_name); ?>Data = $this-><?= lcfirst($entity_name); ?>DataFactory->create();
            $domainId = $domainConfig->getId();

            // $<?= lcfirst($entity_name); ?>Data-> // set data object properties here

            $this->create<?= $entity_name; ?>($<?= lcfirst($entity_name); ?>Data, $domainId, self::<?= strtoupper($entity_name); ?>_REFERENCE);
        }
    }

    private function create<?= $entity_name; ?>(<?= $entity_name; ?>Data $<?= lcfirst($entity_name); ?>Data, int $domainId, ?string $referenceName = null): void
    {
        $<?= lcfirst($entity_name); ?> = $this-><?= lcfirst($entity_name); ?>Facade->create($<?= lcfirst($entity_name); ?>Data);

        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $<?= lcfirst($entity_name); ?>, $domainId);
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
