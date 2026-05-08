<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\FlashMessage\ErrorExtractor;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;

class AdminBaseController extends AbstractController
{
    use FlashMessageTrait;

    #[Required]
    public AccessCheckerInterface $accessChecker;

    #[Required]
    public ErrorExtractor $errorExtractor;

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
}
