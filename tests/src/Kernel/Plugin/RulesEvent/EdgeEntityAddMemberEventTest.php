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

use Drupal\apigee_edge_teams\Entity\Team;
use Drupal\rules\Context\ContextConfig;

/**
 * Tests Edge entity add_member event.
 *
 * @package Drupal\Tests\apigee_actions\Kernel
 */
class EdgeEntityAddMemberEventTest extends EdgeEntityEventTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'apigee_actions',
    'apigee_edge',
    'apigee_edge_teams',
    'apigee_mock_api_client',
    'dblog',
    'key',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('team_member_role');
  }

  /**
   * Tests add_member events for Edge entities.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\rules\Exception\LogicException
   */
  public function testInsertEvent() {
    // Create an insert rule.
    $rule = $this->expressionManager->createRule();
    $rule->addAction('apigee_actions_log_message',
      ContextConfig::create()
        ->setValue('message', "Member {{ member.first_name }} was added to team {{ team.displayName }}.")
        ->process('message', 'rules_tokens')
    );

    $config_entity = $this->storage->create([
      'id' => 'app_add_member_rule',
      'events' => [['event_name' => 'apigee_actions_entity_add_member:team']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // Create a new team.
    /** @var \Drupal\apigee_edge_teams\Entity\TeamInterface $team */
    $team = Team::create([
      'name' => $this->randomMachineName(),
      'displayName' => $this->randomGenerator->name(),
    ]);
    $this->stack->queueMockResponse([
      'get_team' => [
        'team' => $team,
      ],
    ]);
    $this->queueDeveloperResponse($this->account);
    $team->save();

    // Add team member.
    $this->stack->queueMockResponse([
      'get_team' => [
        'team' => $team,
      ],
    ]);
    $this->queueDeveloperResponse($this->account);
    $this->container->get('apigee_edge_teams.team_membership_manager')->addMembers($team->id(), [
      $this->account->getEmail(),
    ]);

    $this->assertLogsContains("Event apigee_actions_entity_add_member:team was dispatched.");
    $this->assertLogsContains("Member {$this->account->first_name->value} was added to team {$team->getDisplayName()}.");
  }

}
