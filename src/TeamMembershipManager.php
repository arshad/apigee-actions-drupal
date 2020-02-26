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

namespace Drupal\apigee_actions;

use Drupal\apigee_actions\Event\EdgeEntityEvent;
use Drupal\apigee_edge\Entity\Controller\DeveloperControllerInterface;
use Drupal\apigee_edge\Entity\DeveloperCompaniesCacheInterface;
use Drupal\apigee_edge_teams\CompanyMembersControllerFactoryInterface;
use Drupal\apigee_edge_teams\TeamMembershipManagerInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Decorates the apigee_edge_teams.team_membership_manager service.
 */
class TeamMembershipManager implements TeamMembershipManagerInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * The team membership manager service.
   *
   * @var \Drupal\apigee_edge_teams\TeamMembershipManagerInterface
   */
  private $inner;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * The apigee_actions logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $apigeeActionsLogger;

  /**
   * The company members controller factory service.
   *
   * @var \Drupal\apigee_edge_teams\CompanyMembersControllerFactoryInterface
   */
  private $companyMembersControllerFactory;

  /**
   * The developer companies cache.
   *
   * @var \Drupal\apigee_edge\Entity\DeveloperCompaniesCacheInterface
   */
  private $developerCompaniesCache;

  /**
   * The developer controller service.
   *
   * @var \Drupal\apigee_edge\Entity\Controller\DeveloperControllerInterface
   */
  private $developerController;

  /**
   * The cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private $cacheTagsInvalidator;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * TeamMembershipManager constructor.
   *
   * @param \Drupal\apigee_edge_teams\TeamMembershipManagerInterface $inner
   *   The Apigee Edge team manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\apigee_edge_teams\CompanyMembersControllerFactoryInterface $company_members_controller_factory
   *   The company members controller factory service.
   * @param \Drupal\apigee_edge\Entity\Controller\DeveloperControllerInterface $developer_controller
   *   The developer controller service.
   * @param \Drupal\apigee_edge\Entity\DeveloperCompaniesCacheInterface $developer_companies_cache
   *   The developer companies cache.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(TeamMembershipManagerInterface $inner, EntityTypeManagerInterface $entity_type_manager, CompanyMembersControllerFactoryInterface $company_members_controller_factory, DeveloperControllerInterface $developer_controller, DeveloperCompaniesCacheInterface $developer_companies_cache, CacheTagsInvalidatorInterface $cache_tags_invalidator, LoggerChannelFactoryInterface $logger_factory, EventDispatcherInterface $event_dispatcher) {
    $this->inner = $inner;
    $this->entityTypeManager = $entity_type_manager;
    $this->companyMembersControllerFactory = $company_members_controller_factory;
    $this->developerController = $developer_controller;
    $this->developerCompaniesCache = $developer_companies_cache;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->loggerFactory = $logger_factory;
    $this->logger = $this->loggerFactory->get('apigee_edge_teams');
    $this->apigeeActionsLogger = $this->loggerFactory->get('apigee_actions');
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function getMembers(string $team): array {
    return $this->inner->getMembers($team);
  }

  /**
   * {@inheritdoc}
   */
  public function addMembers(string $team, array $developers): void {
    $this->inner->addMembers($team, $developers);

    $dispatched_event_name = 'apigee_actions_entity_add_member:team';
    $team = $this->entityTypeManager->getStorage('team')->load($team);
    $users_by_mail = array_reduce($this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $developers]), function (array $carry, UserInterface $user) {
      $carry[$user->getEmail()] = $user;
      return $carry;
    }, []);

    // Dispatch an event for each developer.
    foreach ($developers as $developer) {
      $this->apigeeActionsLogger->notice("Event $dispatched_event_name was dispatched.");
      $this->eventDispatcher->dispatch($dispatched_event_name, new EdgeEntityEvent($team, [
        'team' => $team,
        'member' => $users_by_mail[$developer],
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeMembers(string $team, array $developers): void {
    $this->inner->removeMembers($team, $developers);
  }

  /**
   * {@inheritdoc}
   */
  public function getTeams(string $developer): array {
    return $this->inner->getTeams($developer);
  }

}
