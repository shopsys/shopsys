<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?>
{
    <?php if ($entity_config->isTranslatable || $entity_config->isMultiDomain): ?>
        public function __construct(
            private readonly Domain $domain,
        ) {
        }
    <?php endif; ?>

    public function create(): <?= $entity_config->entityName; ?>Data
    {
        $<?= lcfirst($entity_config->entityName); ?>Data = $this->createInstance();
        $this->fillNew($<?= lcfirst($entity_config->entityName); ?>Data);

        return $<?= lcfirst($entity_config->entityName); ?>Data;
    }

    public function createFrom<?= $entity_config->entityName; ?>(<?= $entity_config->entityName; ?> $<?= lcfirst($entity_config->entityName); ?>): <?= $entity_config->entityName; ?>Data
    {
        $<?= lcfirst($entity_config->entityName); ?>Data = $this->createInstance();

        <?php if ($entity_config->hasUuid): ?>
            $<?= lcfirst($entity_config->entityName); ?>Data->uuid = $<?= lcfirst($entity_config->entityName); ?>->getUuid();
        <?php endif; ?>

        <?php if ($entity_config->isTranslatable): ?>
            $translations = $<?= lcfirst($entity_config->entityName); ?>->getTranslations();

            foreach ($translations as $translate) {
                <?php foreach ($entity_config->getTranslationPropertiesOnly() as $translationProperty): ?>
                    $<?= lcfirst($entity_config->entityName); ?>Data-><?= $translationProperty->propertyName; ?>[$translate->getLocale()] = $translate-><?= $translationProperty->getGetterName(); ?>();
                <?php endforeach; ?>
            }
        <?php endif; ?>

        <?php if ($entity_config->isMultiDomain): ?>
            foreach ($this->domain->getAllIds() as $domainId) {
            <?php foreach ($entity_config->getDomainPropertiesOnly() as $domainProperty): ?>
                $<?= lcfirst($entity_config->entityName); ?>Data-><?= $domainProperty->propertyName; ?>[$domainId] = $<?= lcfirst($entity_config->entityName); ?>-><?= $domainProperty->getGetterName(); ?>($domainId);
            <?php endforeach; ?>
            }
        <?php endif; ?>

        <?php foreach ($entity_config->getEntityPropertiesOnly() as $property): ?>
            $<?= lcfirst($entity_config->entityName); ?>Data-><?= $property->propertyName; ?> = $<?= lcfirst($entity_config->entityName); ?>-><?= $property->getGetterName(); ?>();
        <?php endforeach; ?>

        return $<?= lcfirst($entity_config->entityName); ?>Data;
    }

    private function createInstance(): <?= $entity_config->entityName; ?>Data
    {
        return new <?= $entity_config->entityName; ?>Data();
    }

    private function fillNew(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
        // TODO set default values here if necessary
        <?php if ($entity_config->isTranslatable): ?>
            foreach ($this->domain->getAllLocales() as $locale) {
                <?php foreach ($entity_config->getTranslationPropertiesOnly() as $translationProperty): ?>
                    $<?= lcfirst($entity_config->entityName); ?>Data-><?= $translationProperty->propertyName; ?>[$locale] = null;
                <?php endforeach; ?>
            }
        <?php endif; ?>

        <?php if ($entity_config->isMultiDomain): ?>
            foreach ($this->domain->getAllIds() as $domainId) {
            <?php foreach ($entity_config->getDomainPropertiesOnly() as $domainProperty): ?>
                $<?= lcfirst($entity_config->entityName); ?>Data-><?= $domainProperty->propertyName; ?>[$domainId] = null;
            <?php endforeach; ?>
            }
        <?php endif; ?>
    }
}
