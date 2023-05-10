<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Setting\Setting;
use App\Form\Admin\CspHeaderSettingFormType;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CspHeaderController extends AdminBaseController
{
    /**
     * @var \App\Component\Setting\Setting
     */
    private Setting $setting;

    /**
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @Route("superadmin/csp-header-setting/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingAction(Request $request): Response
    {
        $formData = ['cspHeader' => $this->setting->get(Setting::CSP_HEADER)];

        $form = $this->createForm(CspHeaderSettingFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setting->set(Setting::CSP_HEADER, $form->getData()['cspHeader']);
            $this->addSuccessFlashTwig(t('Content-Security-Policy header has been set.'));
        }

        return $this->render('Admin/Content/CspHeader/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
