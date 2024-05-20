<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
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
}
