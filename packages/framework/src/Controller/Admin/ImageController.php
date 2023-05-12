<?php

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
     */
    public function overviewAction()
    {
        $imageEntityConfigs = $this->imageFacade->getAllImageEntityConfigsByClass();

        return $this->render('@ShopsysFramework/Admin/Content/Image/overview.html.twig', [
            'imageEntityConfigs' => $imageEntityConfigs,
        ]);
    }
}
