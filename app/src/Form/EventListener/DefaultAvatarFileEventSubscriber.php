<?php
/**
 * Default photo file event subscriber.
 */

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class DefaultAvatarFileEventSubscriber.
 */
class DefaultAvatarFileEventSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SUBMIT => 'preSubmitData'];
    }

    /**
     * Pre submit handler.
     *
     * @param \Symfony\Component\Form\FormEvent $event Event
     */
    public function preSubmitData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (null === $data['file']) {
            $data['file'] = ($form->getData())->getFile();
            $event->setData($data);
        }
    }
}
