<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class KitchenController extends FrontBaseController
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Kitchen/index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('Front/Content/Kitchen/list.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listModernKitchenAction(): Response
    {
        return $this->render('Front/Content/Kitchen/variants/modernKitchen.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listRusticalKitchenAction(): Response
    {
        return $this->render('Front/Content/Kitchen/variants/rusticalKitchen.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listClassicKitchenAction(): Response
    {
        return $this->render('Front/Content/Kitchen/variants/classicKitchen.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(): Response
    {
        return $this->render('Front/Content/Kitchen/detail.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionFlash(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/flash.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionTouch(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/touch.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionSpeed(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/speed.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionLux(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/lux.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionStoneart(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/stoneart.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionRiva(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/riva.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionStructura(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/structura.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionEasytouch(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/easytouch.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionFashion(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/fashion.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionInline(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/inline.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionInox(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/inox.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionLaser(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/laser.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionRio(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/rio.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionPura(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/pura.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionFocus(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/focus.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionCemento(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/cemento.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionCascada(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/cascada.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionCastello(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/castello.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionChalet(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/chalet.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionGent(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/gent.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionYork(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/york.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionSylt(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/sylt.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionKansas(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/kansas.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailActionCredo(): Response
    {
        return $this->render('Front/Content/Kitchen/detail/credo.html.twig');
    }
}
