<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(ImageFacade $imageFacade)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @Route("/image/overview/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(): Response
    {
        $imageEntityConfigs = $this->imageFacade->getAllImageEntityConfigsByClass();

        return $this->render('@ShopsysFramework/Admin/Content/Image/overview.html.twig', [
            'imageEntityConfigs' => $imageEntityConfigs,
        ]);
    }
}
