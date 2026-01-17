<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\CspHeaderSetting\CspHeaderSettingFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class CspHeaderController extends AdminBaseController
{
    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    #[Route(path: 'superadmin/csp-header-setting/')]
    public function settingAction(Request $request): Response
    {
        $formData = ['cspHeader' => $this->setting->get(Setting::CSP_HEADER)];

        $form = $this->createForm(CspHeaderSettingFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setting->set(Setting::CSP_HEADER, $form->getData()['cspHeader']);
            $this->addSuccessFlashTwig(t('Content-Security-Policy header has been set.'));
        }

        return $this->render('@ShopsysAdministration/content/cspHeader/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
