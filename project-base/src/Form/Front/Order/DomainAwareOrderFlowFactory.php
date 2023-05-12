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
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Craue\FormFlowBundle\Storage\DataManager $dataManager
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly FormFactoryInterface $formFactory,
        private readonly DataManager $dataManager
    ) {
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
