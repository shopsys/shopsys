<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Cookies\CookiesSettingFormType;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CookiesController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     */
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly CookiesFacade $cookiesFacade,
    ) {
    }

    /**
     * @Route("/cookies/setting/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function settingAction(Request $request)
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $cookiesArticle = $this->cookiesFacade->findCookiesArticleByDomainId($selectedDomainId);

        $form = $this->createForm(CookiesSettingFormType::class, ['cookiesArticle' => $cookiesArticle], [
            'domain_id' => $selectedDomainId,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cookiesArticle = $form->getData()['cookiesArticle'];

            $this->cookiesFacade->setCookiesArticleOnDomain(
                $cookiesArticle,
                $selectedDomainId,
            );

            $this->addSuccessFlashTwig(t('Cookies information settings modified.'));

            return $this->redirectToRoute('admin_cookies_setting');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Cookies/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
