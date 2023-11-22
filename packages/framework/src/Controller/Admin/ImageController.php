<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(protected readonly ImageFacade $imageFacade)
    {
    }

    /**
     * @Route("/image/overview/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(): \Symfony\Component\HttpFoundation\Response
    {
        $imageEntityConfigs = $this->imageFacade->getAllImageEntityConfigsByClass();

        return $this->render('@ShopsysFramework/Admin/Content/Image/overview.html.twig', [
            'imageEntityConfigs' => $imageEntityConfigs,
        ]);
    }
}
