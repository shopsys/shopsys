<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends CRUDController
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
    ) {
    }


    public function listAction(Request $request): Response
    {
        $this->admin->checkAccess('list');

        $categoriesWithPreloadedChildren = $this->categoryFacade->getAllCategoriesWithPreloadedChildren(
            $request->getLocale(),
        );

        return $this->render('@ShopsysAdministration/Category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
        ]);
    }

}
