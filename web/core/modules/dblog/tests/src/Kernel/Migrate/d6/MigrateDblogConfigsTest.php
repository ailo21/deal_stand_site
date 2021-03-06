<?php

namespace Drupal\Tests\dblog\Kernel\Migrate\d6;

use Drupal;
use Drupal\Tests\SchemaCheckTestTrait;
use Drupal\Tests\migrate_drupal\Kernel\d6\MigrateDrupal6TestBase;

/**
 * Upgrade variables to dblog.settings.yml.
 *
 * @group migrate_drupal_6
 */
class MigrateDblogConfigsTest extends MigrateDrupal6TestBase {

  use SchemaCheckTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('d6_dblog_settings');
  }

  /**
   * Tests migration of dblog variables to dblog.settings.yml.
   */
  public function testBookSettings() {
    $config = $this->config('dblog.settings');
    $this->assertSame(10000, $config->get('row_limit'));
    $this->assertConfigSchema(Drupal::service('config.typed'), 'dblog.settings', $config->get());
  }

}
