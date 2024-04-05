<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Controller\Admin\AdvertController as BaseAdvertController;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Advert\AdvertFacade $advertFacade
 * @property \App\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
 * @property \App\Model\Advert\AdvertDataFactory $advertDataFactory
 * @method __construct(\App\Model\Advert\AdvertFacade $advertFacade, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Twig\ImageExtension $imageExtension, \App\Model\Advert\AdvertDataFactory $advertDataFactory, \App\Model\Advert\AdvertPositionRegistry $advertPositionRegistry, \Doctrine\ORM\EntityManagerInterface $entityManager)
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 */
class AdvertController extends BaseAdvertController
{
    /**
     * @Route("/advert/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $advert = $this->advertFacade->getById($id);

        $advertData = $this->advertDataFactory->createFromAdvert($advert);

        $form = $this->createForm(AdvertFormType::class, $advertData, [
            'web_image_exists' => $this->imageExtension->imageExists($advert, AdvertFacade::IMAGE_TYPE_WEB),
            'mobile_image_exists' => $this->imageExtension->imageExists($advert, AdvertFacade::IMAGE_TYPE_MOBILE),
            'scenario' => AdvertFormType::SCENARIO_EDIT,
            'advert' => $advert,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->advertFacade->edit($id, $advertData);

            $this->addSuccessFlashTwig(
                t('Advertising <a href="{{ url }}"><strong>{{ name }}</strong></a> modified'),
                [
                    'name' => $advert->getName(),
                    'url' => $this->generateUrl('admin_advert_edit', ['id' => $advert->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_advert_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing advertising - %name%', ['%name%' => $advert->getName()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Advert/edit.html.twig', [
            'form' => $form->createView(),
            'advert' => $advert,
        ]);
    }
}
