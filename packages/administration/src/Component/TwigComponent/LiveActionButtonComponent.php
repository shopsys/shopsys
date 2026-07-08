<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\TwigComponent;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Admin:LiveActionButton',
    template: '@ShopsysAdministration/components/live_action_button.html.twig',
)]
class LiveActionButtonComponent
{
    public string $label;

    public ?string $action = null;

    public ?string $loadingLabel = null;

    public ?string $icon = null;

    public string $buttonClass = 'btn btn-primary';

    public string $type = 'button';

    public ?string $loading = 'addAttribute(disabled)';

    public bool $preventDefault = true;

    /**
     * @var array<string, string|int|float|bool|null>
     */
    public array $liveParams = [];
}
