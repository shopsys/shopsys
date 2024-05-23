<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdministratorController extends CRUDController
{
    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cloneAction(int $id): Response
    {
        return new JsonResponse('cloned' . $id);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param object $object
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function preDelete(Request $request, object $object): ?Response
    {
        if ($object->getId() === 3) {
            $this->addFlash(
                'sonata_flash_error',
                $this->trans(
                    'Administrator %name% cannot be deleted. EVER!',
                    ['%name%' => $this->escapeHtml($this->admin->toString($object))],
                    'SonataAdminBundle',
                ),
            );

            // redirect to edit mode
            return $this->redirectToList();
        }

        return parent::preDelete($request, $object);
    }
}
