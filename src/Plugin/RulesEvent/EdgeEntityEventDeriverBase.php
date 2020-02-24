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

namespace Drupal\apigee_actions\Plugin\RulesEvent;

use Drupal\apigee_actions\EdgeEntityTypeManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Rules event deriver for Apigee Edge entity types.
 *
 * @package Drupal\apigee_actions\Plugin\RulesEvent
 */
abstract class EdgeEntityEventDeriverBase extends DeriverBase implements EdgeEntityEventDeriverInterface {

  use StringTranslationTrait;

  /**
   * The apigee app entity type manager service.
   *
   * @var \Drupal\apigee_actions\EdgeEntityTypeManagerInterface
   */
  protected $apigeeAppEntityTypeManager;

  /**
   * AppEventDeriver constructor.
   *
   * @param \Drupal\apigee_actions\EdgeEntityTypeManagerInterface $apigee_app_entity_type_manager
   *   The apigee app entity type manager service.
   */
  public function __construct(EdgeEntityTypeManagerInterface $apigee_app_entity_type_manager) {
    $this->apigeeAppEntityTypeManager = $apigee_app_entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('apigee_actions.edge_entity_type_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->apigeeAppEntityTypeManager->getEntityTypes() as $entity_type) {
      $this->derivatives[$entity_type->id()] = [
        'label' => $this->getEventActionLabel($entity_type),
        'category' => $entity_type->getLabel(),
        'entity_type_id' => $entity_type->id(),
        'context' => [
          $entity_type->id() => [
            'type' => "entity:{$entity_type->id()}",
            'label' => $entity_type->getLabel(),
          ],
        ],
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
