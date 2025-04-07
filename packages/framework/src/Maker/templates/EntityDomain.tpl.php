<?= "<?php\n"; ?>
<?php /** @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

/**
 * @ORM\Table(name="<?= $entity_config->tableName . '_domains' ?>")
 * @ORM\Entity
 */
class <?= $class_name; ?>
{
    /**
     * @ORM\ManyToOne(targetEntity="<?= $entity_config->getEntityFullyQualifiedName(); ?>", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private <?= $entity_config->entityName; ?> $<?= lcfirst($entity_config->entityName); ?>;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $domainId;

<?php foreach ($entity_config->getDomainPropertiesOnly() as $property): ?>
    <?= PHP_EOL; ?>
<?= $property->getAdditionalInformation(); ?>
    /**<?= PHP_EOL; ?>
     * <?= implode(PHP_EOL . '     * ', $property->getAnnotationLines()) . PHP_EOL; ?>
     */<?= PHP_EOL; ?>
    private <?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>;
    <?= PHP_EOL; ?>
<?php endforeach; ?>

    public function __construct(<?= $entity_config->entityName; ?> $<?= lcfirst($entity_config->entityName); ?>, int $domainId)
    {
        $this-><?= lcfirst($entity_config->entityName); ?> = $<?= lcfirst($entity_config->entityName); ?>;
        $this->domainId = $domainId;
<?php foreach ($entity_config->getDomainPropertiesOnly() as $property): ?>
    <?php if ($property->isCollection()): ?>
        $this-><?= $property->propertyName; ?> = new ArrayCollection();<?= PHP_EOL; ?>
    <?php endif; ?>
<?php endforeach; ?>
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

<?php foreach ($entity_config->getDomainPropertiesOnly() as $property): ?>
    public function <?= $property->getGetterName(); ?>(): <?= $property->getTypeHint(true); ?>
    {
        return $this-><?= $property->propertyName; ?><?= $property->isCollection() ? '->getValues()' : '' ?>;
    }
<?php endforeach; ?>

<?php foreach ($entity_config->getDomainPropertiesOnly() as $property): ?>
    public function set<?= ucfirst($property->propertyName); ?>(<?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>): void
    {
        $this-><?= $property->propertyName; ?> = $<?= $property->propertyName; ?>;
    }
<?php endforeach; ?>
}
