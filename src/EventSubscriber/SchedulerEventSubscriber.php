<?php

namespace Drupal\scheduler_content_moderation_integration\EventSubscriber;

use Drupal\scheduler\SchedulerEvent;
use Drupal\scheduler\SchedulerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handle scheduler events..
 */
class SchedulerEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SchedulerEvents::PUBLISH_IMMEDIATELY][] = ['publishImmediately'];
    return $events;
  }

  /**
   * Operations before Scheduler publishes a node immediately not via cron.
   *
   * @param \Drupal\scheduler\SchedulerEvent $event
   *   The scheduler event.
   */
  public function publishImmediately(SchedulerEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getNode();
    $node->set('moderation_state', $node->publish_state->getValue());

    $event->setNode($node);
  }

}
