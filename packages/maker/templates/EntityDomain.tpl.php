<?php

use Shopsys\MakerBundle\EntityConfig\CollectionTypeHintTypeEnum;
use Shopsys\MakerBundle\EntityConfig\RelationProperty;

?>
<?= "<?php\n"; ?>
<?php /** @var \Shopsys\MakerBundle\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

#[ORM\Table(name: '<?= $table_name ?>')]
#[ORM\Entity]
class <?= $class_name; ?>
{
    #[ORM\ManyToOne(targetEntity: \<?= $entity_config->getEntityFullyQualifiedName(); ?>::class, inversedBy: 'domains')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private <?= $entity_config->entityName; ?> $<?= lcfirst($entity_config->entityName); ?>;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private int $domainId;

<?php foreach ($entity_config->getDomainPropertiesOnly() as $property): ?>
    <?= PHP_EOL; ?>
<?= $property->getAdditionalInformation(); ?>
<?php if ($property instanceof RelationProperty && $property->getVarDocBlock()): ?>
    /**
     * <?= $property->getVarDocBlock() . PHP_EOL; ?>
     */
<?php endif; ?>
    <?= implode(PHP_EOL . '    ', $property->getAttributeLines()) . PHP_EOL; ?>
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
    public function <?= $property->getGetterName(); ?>(): <?= $property->getTypeHint(CollectionTypeHintTypeEnum::ARRAY); ?>
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
