<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Section;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shopsys.role_section_provider')]
abstract class AbstractRoleSectionProvider
{
    public const string OTHER = 'other';

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection>
     */
    protected array $definedSections = [];

    public function __construct()
    {
        $this->defineSections();
    }

    abstract protected function defineSections(): void;

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    abstract public static function getTargetContext(): string;

    protected function addSection(RoleSection $roleSection): void
    {
        if (isset($this->definedSections[$roleSection->getIdentifier()])) {
            throw new InvalidArgumentException(sprintf(
                'Role section with identifier "%s" is already defined.',
                $roleSection->getIdentifier(),
            ));
        }

        $this->definedSections[$roleSection->getIdentifier()] = $roleSection;
    }

    public function getById(?string $sectionIdentifier): RoleSection
    {
        if ($sectionIdentifier === null || !isset($this->definedSections[$sectionIdentifier])) {
            return self::getDefaultSection();
        }

        return $this->definedSections[$sectionIdentifier];
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection>
     */
    public function getAll(): array
    {
        $sections = $this->definedSections;
        uasort($sections, fn (RoleSection $a, RoleSection $b) => $a->getPriority() <=> $b->getPriority());

        return $sections;
    }

    public static function getDefaultSection(): RoleSection
    {
        return new RoleSection(self::OTHER, t('Other'), 999, null);
    }
}
