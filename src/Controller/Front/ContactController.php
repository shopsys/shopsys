<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Response;

class ContactController extends FrontBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Contacts/contacts.html.twig');
    }
}
