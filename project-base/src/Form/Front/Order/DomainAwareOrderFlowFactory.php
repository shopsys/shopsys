<?php

declare(strict_types=1);

namespace App\Form\Front\Order;

use Craue\FormFlowBundle\Storage\DataManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\OrderFlowFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DomainAwareOrderFlowFactory implements OrderFlowFactoryInterface
{
    private Domain $domain;

    private EventDispatcherInterface $eventDispatcher;

    private RequestStack $requestStack;

    private FormFactoryInterface $formFactory;

    private DataManager $dataManager;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Craue\FormFlowBundle\Storage\DataManager $dataManager
     */
    public function __construct(
        Domain $domain,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        FormFactoryInterface $formFactory,
        DataManager $dataManager
    ) {
        $this->domain = $domain;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->dataManager = $dataManager;
    }

    /**
     * @return \App\Form\Front\Order\OrderFlow
     */
    public function create()
    {
        $orderFlow = new OrderFlow();
        $orderFlow->setDomainId($this->domain->getId());

        // see vendor/craue/formflow-bundle/Resources/config/form_flow.xml
        $orderFlow->setDataManager($this->dataManager);
        $orderFlow->setFormFactory($this->formFactory);
        $orderFlow->setRequestStack($this->requestStack);
        $orderFlow->setEventDispatcher($this->eventDispatcher);

        return $orderFlow;
    }
}
