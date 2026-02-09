<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Codeception;

use Override;

/**
 * Representation of a graphical checkbox that is used in administration
 * Allows to manipulate checkboxes and read their state
 * (The original input is hidden by JS and replaced by a graphical element, therefore it cannot be manipulated directly)
 */
class AdminCheckbox extends AbstractCheckbox
{
    public static function createByCss(ActorInterface $tester, string $cssSelector): static
    {
        return new static($tester, $cssSelector);
    }

    /**
     * Method will mark the particular image element with a generated class via JS so it can be targeted by Selenium easily.
     */
    #[Override]
    protected function getImageElementClass(): string
    {
        $imageElementClass = 'js-checkbox-image-' . random_int(0, getrandmax());

        $script = sprintf(
            '$("%s").next(".css-checkbox__image").addClass("%s")',
            $this->cssSelector,
            $imageElementClass,
        );
        $this->tester->executeJS($script);

        return $imageElementClass;
    }
}
