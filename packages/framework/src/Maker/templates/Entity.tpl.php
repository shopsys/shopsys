<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[] $entity_properties */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

/**
 * @ORM\Table(name="<?= $entity_config->tableName ?>")
 * @ORM\Entity
 */
class <?= $class_name; ?><?php if ($entity_config->isTranslatable): ?> extends AbstractTranslatableEntity<?php endif ?>
{
<?php if ($entity_config->hasId): ?>
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    <?php if ($entity_config->isTranslatable): ?>protected<?php else: ?>private int<?php endif ?> $id;
<?php endif ?>

<?php if ($entity_config->hasUuid): ?>
    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;
<?php endif ?>

<?php if ($entity_config->isTranslatable): ?>
    /**
     * @var \Doctrine\Common\Collections\Collection<int, \<?= $entity_config->getEntityFullyQualifiedName(); ?>Translation>
     * @Prezent\Translations(targetEntity="<?= $entity_config->getEntityFullyQualifiedName(); ?>Translation")
     */
    protected $translations;
<?php endif ?>

<?php foreach ($entity_properties as $property): ?>
    /**<?= PHP_EOL; ?>
     * <?= implode(PHP_EOL . '     * ', $property->getAnnotationLines()) . PHP_EOL; ?>
     */<?= PHP_EOL; ?>
    private <?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>;
    <?= PHP_EOL; ?>
<?php endforeach; ?>

    public function __construct(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data)
    {
<?php if ($entity_config->hasUuid): ?>
        $this->uuid = $<?= lcfirst($entity_config->entityName); ?>Data->uuid ?? Uuid::uuid4()->toString();
<?php endif ?>
<?php if ($entity_config->isTranslatable): ?>
        $this->translations = new ArrayCollection();
<?php endif ?>

        $this->setData($<?= lcfirst($entity_config->entityName); ?>Data);
    }

    public function edit(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
        $this->setData($<?= lcfirst($entity_config->entityName); ?>Data);
    }

    private function setData(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
<?php if ($entity_config->isTranslatable): ?>
        $this->setTranslations($<?= lcfirst($entity_config->entityName); ?>Data);
<?php endif ?>

<?php foreach ($entity_properties as $property): ?>
        $this-><?= $property->propertyName; ?> = $<?= lcfirst($entity_config->entityName); ?>Data-><?= $property->propertyName; ?>;
<?php endforeach; ?>
    }

<?php if ($entity_config->isTranslatable): ?>
    protected function createTranslation(): <?= $entity_config->entityName; ?>Translation
    {
        return new <?= $entity_config->entityName; ?>Translation();
    }

    private function setTranslations(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
        // TODO set translations here for all translatable attributes, e.g.:
        foreach ($<?= lcfirst($entity_config->entityName); ?>Data->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }
<?php endif ?>

<?php if ($entity_config->hasId): ?>
    public function getId(): int
    {
        return $this->id;
    }
<?php endif ?>

<?php if ($entity_config->hasUuid): ?>
    public function getUuid(): string
    {
        return $this->uuid;
    }
<?php endif ?>

<?php foreach ($entity_properties as $property): ?>
    public function <?= $property->getGetterName(); ?>: <?= $property->getTypeHint(); ?>
    {
        return $this-><?= $property->propertyName; ?>;
    }
    <?= PHP_EOL; ?>
<?php endforeach; ?>
}
