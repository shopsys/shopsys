<?php

use Shopsys\MakerBundle\EntityConfig\CollectionTypeHintTypeEnum;
use Shopsys\MakerBundle\EntityConfig\EntityTypeEnum;

?>
<?= "<?php\n"; ?>
<?php /** @var \Shopsys\MakerBundle\EntityConfig\EntityConfig $entity_config */ ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

<?php if ($entity_config->isTranslatable): ?>
/**
 * @method \<?= $entity_config->getEntityFullyQualifiedName(EntityTypeEnum::TRANSLATION); ?> translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \<?= $entity_config->getEntityFullyQualifiedName(EntityTypeEnum::TRANSLATION); ?>> getTranslations()
 */
<?php endif; ?>
#[ORM\Table(name: '<?= $entity_config->tableName ?>')]
#[ORM\Entity]
class <?= $class_name; ?><?php if ($entity_config->isTranslatable): ?> extends AbstractTranslatableEntity<?php endif ?>
{
<?php if ($entity_config->hasId): ?>
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    <?php if ($entity_config->isTranslatable): ?>protected<?php else: ?>private int<?php endif ?> $id;
<?php endif ?>

<?php if ($entity_config->hasUuid): ?>
    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    private string $uuid;
<?php endif ?>

<?php if ($entity_config->isTranslatable): ?>
    /**
     * @var \Doctrine\Common\Collections\Collection<string, \<?= $entity_config->getEntityFullyQualifiedName(EntityTypeEnum::TRANSLATION); ?>>
     */
    #[Prezent\Translations(targetEntity: \<?= $entity_config->getEntityFullyQualifiedName(EntityTypeEnum::TRANSLATION); ?>::class)]
    protected $translations;
<?php endif ?>

<?php if ($entity_config->isMultiDomain): ?>
    /**
     * @var \Doctrine\Common\Collections\Collection<int, \<?= $entity_config->getEntityFullyQualifiedName(EntityTypeEnum::DOMAIN); ?>>
     */
    #[ORM\OneToMany(targetEntity: \<?= $entity_config->getEntityFullyQualifiedName(EntityTypeEnum::DOMAIN); ?>::class, mappedBy: '<?= lcfirst($entity_config->entityName) ?>', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $domains;
<?php endif ?>

<?php foreach ($entity_config->getEntityPropertiesOnly() as $property): ?>
    <?= PHP_EOL; ?>
<?= $property->getAdditionalInformation(); ?>
<?php if ($property instanceof \Shopsys\MakerBundle\EntityConfig\RelationProperty && $property->getVarDocBlock()): ?>
    /**
     * <?= $property->getVarDocBlock() . PHP_EOL; ?>
     */
<?php endif; ?>
    <?= implode(PHP_EOL . '    ', $property->getAttributeLines()) . PHP_EOL; ?>
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
<?php if ($entity_config->isMultiDomain): ?>
        $this->domains = new ArrayCollection();
        $this->createDomains($<?= lcfirst($entity_config->entityName); ?>Data);
<?php endif ?>
<?php foreach ($entity_config->getEntityPropertiesOnly() as $property): ?>
        <?php if ($property->isCollection()): ?>
            $this-><?= $property->propertyName; ?> = new ArrayCollection();<?= PHP_EOL; ?>
        <?php endif; ?>
<?php endforeach; ?>

        $this->setData($<?= lcfirst($entity_config->entityName); ?>Data);
    }

    public function edit(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
<?php if ($entity_config->isMultiDomain): ?>
        $this->setDomains($<?= lcfirst($entity_config->entityName); ?>Data);
<?php endif ?>
        $this->setData($<?= lcfirst($entity_config->entityName); ?>Data);
    }

    private function setData(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
<?php if ($entity_config->isTranslatable): ?>
        $this->setTranslations($<?= lcfirst($entity_config->entityName); ?>Data);
<?php endif ?>

<?php foreach ($entity_config->getEntityPropertiesOnly() as $property): ?>
    <?php if ($property->isCollection() === false): ?>
        $this-><?= $property->propertyName; ?> = $<?= lcfirst($entity_config->entityName); ?>Data-><?= $property->propertyName; ?>;
    <?php endif; ?>
<?php endforeach; ?>
    }

<?php if ($entity_config->isTranslatable): ?>
    protected function createTranslation(): <?= $entity_config->entityName; ?>Translation
    {
        return new <?= $entity_config->entityName; ?>Translation();
    }

    private function setTranslations(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
    <?php foreach ($entity_config->getTranslationPropertiesOnly() as $property): ?>
        foreach ($<?= lcfirst($entity_config->entityName); ?>Data-><?= $property->propertyName; ?> as $locale => $<?= $property->propertyName; ?>) {
            $this->translation($locale)->set<?= ucfirst($property->propertyName); ?>($<?= $property->propertyName; ?>);
        }
    <?php endforeach; ?>
    }
<?php endif ?>

<?php if ($entity_config->isMultiDomain): ?>
    private function setDomains(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
        foreach ($this->domains as $<?= lcfirst($entity_config->entityName); ?>Domain) {
            $domainId = $<?= lcfirst($entity_config->entityName); ?>Domain->getDomainId();
            <?php foreach ($entity_config->getDomainPropertiesOnly() as $property): ?>
                $<?= lcfirst($entity_config->entityName); ?>Domain->set<?= ucfirst($property->propertyName); ?>($<?= lcfirst($entity_config->entityName); ?>Data-><?= $property->propertyName; ?>[$domainId]);
            <?php endforeach; ?>
        }
    }

    private function createDomains(<?= $entity_config->entityName; ?>Data $<?= lcfirst($entity_config->entityName); ?>Data): void
    {
    <?php if (count($entity_config->getDomainPropertiesOnly()) > 0): ?>
            $domainIds = array_keys($<?= lcfirst($entity_config->entityName); ?>Data-><?= $entity_config->findFirstDomainProperty()?->propertyName; ?>);

            foreach ($domainIds as $domainId) {
                $<?= lcfirst($entity_config->entityName); ?>Domain = new <?= $entity_config->entityName; ?>Domain($this, $domainId);
                $this->domains->add($<?= lcfirst($entity_config->entityName); ?>Domain);
            }

            $this->setDomains($<?= lcfirst($entity_config->entityName); ?>Data);
    <?php endif; ?>
    }

    private function get<?= $entity_config->entityName; ?>Domain(int $domainId):  <?= $entity_config->entityName; ?>Domain
    {
        foreach ($this->domains as $<?= lcfirst($entity_config->entityName); ?>Domain) {
            if ($<?= lcfirst($entity_config->entityName); ?>Domain->getDomainId() === $domainId) {
                return $<?= lcfirst($entity_config->entityName); ?>Domain;
            }
        }

        throw new <?= $entity_config->entityName; ?>DomainNotFoundException($domainId, $this->id);
    }
<?php endif; ?>

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

<?php foreach ($entity_config->getAllProperties() as $property): ?>
    public function <?= $property->getGetterName(); ?>(<?php if ($property->isForTranslation()): ?>?string $locale = null<?php elseif ($property->isForDomain()): ?>int $domainId<?php endif ?>): <?= $property->getTypeHint(CollectionTypeHintTypeEnum::ARRAY); ?>
    {
        <?php if ($property->isForTranslation()): ?>
            return $this->translation($locale)-><?= $property->getGetterName(); ?>();
        <?php elseif ($property->isForDomain()): ?>
            return $this->get<?= $entity_config->entityName; ?>Domain($domainId)-><?= $property->getGetterName(); ?>();
        <?php else: ?>
            return $this-><?= $property->propertyName; ?><?= $property->isCollection() ? '->getValues()' : '' ?>;
        <?php endif ?>
    }
    <?= PHP_EOL; ?>
<?php endforeach; ?>
}
