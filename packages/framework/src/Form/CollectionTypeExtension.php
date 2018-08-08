<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Form\ResizeFormListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener as SymfonyResizerFormListener;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Make CollectionType use custom ResizeFormListener
 */
class CollectionTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->removeOriginalResizeFormListener($builder->getEventDispatcher());

        $resizeListener = new ResizeFormListener(
            $options['entry_type'],
            $options['entry_options'],
            $options['allow_add'],
            $options['allow_delete'],
            $options['delete_empty']
        );

        $builder->addEventSubscriber($resizeListener);
    }

    public function getExtendedType()
    {
        return CollectionType::class;
    }

    private function removeOriginalResizeFormListener(EventDispatcherInterface $eventDispatcher)
    {
        $listenersByEventName = $eventDispatcher->getListeners();

        foreach ($listenersByEventName as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if (isset($listener[0])) {
                    if ($listener[0] instanceof SymfonyResizerFormListener) {
                        $eventDispatcher->removeListener($eventName, $listener);
                    }
                }
            }
        }
    }
}
