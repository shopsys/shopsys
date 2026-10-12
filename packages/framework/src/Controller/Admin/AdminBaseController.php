<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Service\Attribute\Required;

class AdminBaseController extends AbstractController
{
    use FlashMessageTrait;

    #[Required]
    public AccessCheckerInterface $accessChecker;

    #[Required]
    public ErrorExtractor $errorExtractor;

    #[Required]
    public FlashMessageService $flashMessageService;

    protected function getCurrentAdministrator(): Administrator
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null $administrator */
        $administrator = $this->getUser();

        if ($administrator === null) {
            throw $this->createAccessDeniedException('No administrator is logged in.');
        }

        return $administrator;
    }

    protected function createInvalidJsonResponse(FormInterface $form): JsonResponse
    {
        return new JsonResponse([
            'result' => 'invalid',
            'errors' => $this->errorExtractor->getAllErrorsAsArray($form, $this->getErrorMessages()),
        ]);
    }

    /**
     * Redirects back to the edit page when the "apply" button of the action bar was clicked, to the list page otherwise
     *
     * @param array<string, mixed> $editRouteParameters
     */
    protected function redirectAfterSave(
        FormInterface $form,
        string $listRoute,
        string $editRoute,
        array $editRouteParameters,
        string $actionBarFormTypeName = 'actionBar',
    ): RedirectResponse {
        $applyButton = $form->has($actionBarFormTypeName) && $form->get($actionBarFormTypeName)->has('apply') ? $form->get($actionBarFormTypeName)->get('apply') : null;

        if ($applyButton instanceof ClickableInterface && $applyButton->isClicked()) {
            return $this->redirectToRoute($editRoute, $editRouteParameters);
        }

        return $this->redirectToRoute($listRoute);
    }
}
