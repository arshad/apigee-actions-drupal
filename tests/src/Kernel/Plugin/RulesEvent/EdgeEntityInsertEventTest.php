<?php

/**
 * Copyright 2020 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\Tests\apigee_actions\Kernel\Plugin\RulesEvent;

use Drupal\apigee_edge\Entity\DeveloperApp;
use Drupal\Core\Database\Database;
use Drupal\rules\Context\ContextConfig;
use Drupal\Tests\apigee_mock_api_client\Traits\ApigeeMockApiClientHelperTrait;
use Drupal\Tests\rules\Kernel\RulesKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests Edge entity insert event.
 *
 * @package Drupal\Tests\apigee_actions\Kernel
 */
class EdgeEntityInsertEventTest extends RulesKernelTestBase {

  use ApigeeMockApiClientHelperTrait {
    apigeeTestHelperSetup as baseSetUp;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'apigee_actions',
    'apigee_edge',
    'apigee_mock_api_client',
    'dblog',
    'key',
    'options',
  ];

  /**
   * The entity storage for Rules config entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->storage = $this->container->get('entity_type.manager')->getStorage('rules_reaction_rule');
    $this->logger = $this->container->get('logger.channel.apigee_actions');

    $this->installConfig(['apigee_edge']);

    // Prepare to create a user.
    $this->installEntitySchema('user');
    $this->installSchema('dblog', ['watchdog']);
    $this->installSchema('system', ['sequences']);
    $this->installSchema('user', ['users_data']);

    $this->baseSetUp();
  }

  /**
   * Tests insert events for Edge entities.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\rules\Exception\LogicException
   */
  public function testInsertEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addAction('apigee_actions_log_message',
      ContextConfig::create()
        ->setValue('message', "App {{ developer_app.name }} was created.")
        ->process('message', 'rules_tokens')
    );

    $config_entity = $this->storage->create([
      'id' => 'app_insert_rule',
      'events' => [['event_name' => 'apigee_actions_entity_insert:developer_app']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    /** @var \Drupal\user\UserInterface $account */
    $account = User::create([
      'mail' => $this->randomMachineName() . '@example.com',
      'name' => $this->randomMachineName(),
      'first_name' => $this->getRandomGenerator()->word(16),
      'last_name' => $this->getRandomGenerator()->word(16),
    ]);
    $account->save();
    $this->queueDeveloperResponse($account, 201);

    /** @var \Drupal\apigee_edge\Entity\DeveloperAppInterface $app */
    $app = DeveloperApp::create([
      'name' => $this->randomMachineName(),
      'displayName' => $this->randomMachineName(),
      'developerId' => $account->uuid(),
    ]);
    $this->stack->queueMockResponse('get-developer-app', [
      'app' => $app,
    ]);
    $app->setOwner($account);
    $app->save();

    $logs = Database::getConnection()->select('watchdog', 'wd')
      ->fields('wd', ['message'])
      ->condition('type', 'apigee_actions')
      ->execute()
      ->fetchCol();

    $this->assertContains("Event apigee_actions_entity_insert:developer_app was dispatched.", $logs);
    $this->assertContains("App {$app->getName()} was created.", $logs);
  }

}
