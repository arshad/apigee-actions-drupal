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

namespace Drupal\apigee_actions\Plugin\BusinessRulesReactsOn;

use Drupal\business_rules\Plugin\BusinessRulesReactsOnPlugin;

/**
 * Defines the app created business rule.
 *
 * @BusinessRulesReactsOn(
 *   id = "apigee_actions_app_created",
 *   label = @Translation("App created"),
 *   description = @Translation("Reacts after a new app has been created."),
 *   group = @Translation("Apigee"),
 *   eventName = "apigee_actions.business_rules.app.created",
 *   hasTargetEntity = FALSE,
 *   hasTargetBundle = FALSE,
 *   priority = 1000,
 * )
 */
class AppCreated extends BusinessRulesReactsOnPlugin {

}
