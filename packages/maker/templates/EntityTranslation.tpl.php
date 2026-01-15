<?= "<?php\n"; ?>
<?php /** @var \Shopsys\MakerBundle\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

#[ORM\Table(name: '<?= $table_name ?>')]
#[ORM\Entity]
class <?= $class_name; ?> extends AbstractTranslation
{
    /**
     * @var \<?= $entity_config->getEntityFullyQualifiedName(); ?><?= PHP_EOL; ?>
     */
    #[Prezent\Translatable(targetEntity: \<?= $entity_config->getEntityFullyQualifiedName(); ?>::class)]
    protected $translatable;

<?php foreach ($entity_config->getTranslationPropertiesOnly() as $property): ?>
    <?= implode(PHP_EOL . '    ', $property->getAttributeLines()) . PHP_EOL; ?>
    private <?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>;
    <?= PHP_EOL; ?>
<?php endforeach; ?>

<?php foreach ($entity_config->getTranslationPropertiesOnly() as $property): ?>
    public function <?= $property->getGetterName(); ?>(): <?= $property->getTypeHint(); ?>
    {
        return $this-><?= $property->propertyName; ?>;
    }
<?php endforeach; ?>

<?php foreach ($entity_config->getTranslationPropertiesOnly() as $property): ?>
    public function set<?= ucfirst($property->propertyName); ?>(<?= $property->getTypeHint() ?> $<?= $property->propertyName; ?>): void
    {
        $this-><?= $property->propertyName; ?> = $<?= $property->propertyName; ?>;
    }
<?php endforeach; ?>
}
