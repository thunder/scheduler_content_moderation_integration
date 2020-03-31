<?php

namespace Drupal\Tests\scheduler_content_moderation_integration\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\content_moderation\Traits\ContentModerationTestTrait;

/**
 * Test covering the TransitionAccessConstraintValidator.
 *
 * @coversDefaultClass \Drupal\scheduler_content_moderation_integration\Plugin\Validation\Constraint\TransitionAccessConstraintValidator
 *
 * @group scheduler
 */
abstract class SchedulerContentModerationBrowserTestBase extends BrowserTestBase {

  use ContentModerationTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['content_moderation', 'scheduler_content_moderation_integration'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ])
      ->setThirdPartySetting('scheduler', 'publish_enable', TRUE)
      ->setThirdPartySetting('scheduler', 'unpublish_enable', TRUE)
      ->save();

    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle('node', 'page');
    $workflow->save();

    $this->schedulerUser = $this->drupalCreateUser([
      'access content',
      'create page content',
      'edit any page content',
      'schedule publishing of nodes',
      'view latest version',
      'view any unpublished content',
      'access content overview',
      'use editorial transition create_new_draft',
      'use editorial transition publish',
      'use editorial transition archive',
    ]);

    $this->restrictedUser = $this->drupalCreateUser([
      'access content',
      'create page content',
      'edit own page content',
      'view latest version',
      'view any unpublished content',
      'access content overview',
      'use editorial transition create_new_draft',
    ]);

  }

}
