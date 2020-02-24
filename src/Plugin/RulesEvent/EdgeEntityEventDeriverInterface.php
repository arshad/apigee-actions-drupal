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

use Drupal\apigee_edge\Entity\EdgeEntityTypeInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

/**
 * Provides an interface for Apigee Edge entity event deriver.
 */
interface EdgeEntityEventDeriverInterface extends ContainerDeriverInterface {

  /**
   * Returns the event's action name. Example: 'insert' or 'delete'.
   *
   * @param \Drupal\apigee_edge\Entity\EdgeEntityTypeInterface $entity_type
   *   The Apigee Edge entity type.
   *
   * @return string
   *   The event's action name.
   */
  public function getEventActionName(EdgeEntityTypeInterface $entity_type): string;

  /**
   * Returns the event's action label. Example: 'After saving a new App'.
   *
   * @param \Drupal\apigee_edge\Entity\EdgeEntityTypeInterface $entity_type
   *   The Apigee Edge entity type.
   *
   * @return string
   *   The event's action name.
   */
  public function getEventActionLabel(EdgeEntityTypeInterface $entity_type): string;

}
