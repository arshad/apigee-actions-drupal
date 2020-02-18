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

namespace Drupal\apigee_actions\Plugin\BusinessRulesAction;

use Drupal\business_rules\ActionInterface;
use Drupal\business_rules\Events\BusinessRulesEvent;
use Drupal\business_rules\ItemInterface;
use Drupal\business_rules\Plugin\BusinessRulesActionPlugin;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the apigee_actions_log_action action.
 *
 * @BusinessRulesAction(
 *   id = "apigee_actions_log_action",
 *   label = @Translation("Log a message"),
 *   group = @Translation("Apigee"),
 *   description = @Translation("Logs a message."),
 *   reactsOnIds = {},
 *   isContextDependent = FALSE,
 *   hasTargetEntity = FALSE,
 *   hasTargetBundle = FALSE,
 *   hasTargetField = FALSE,
 * )
 */
class LogAction extends BusinessRulesActionPlugin {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array &$form, FormStateInterface $form_state, ItemInterface $item) {
    $settings['level'] = [
      '#title' => $this->t('Level'),
      '#type' => 'select',
      '#options' => RfcLogLevel::getLevels(),
      '#required' => TRUE,
      '#default_value' => $item->getSettings('level'),
    ];

    $settings['message'] = [
      '#title' => $this->t('Message'),
      '#type' => 'textarea',
      '#required' => TRUE,
      '#default_value' => $item->getSettings('message'),
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ActionInterface $action, BusinessRulesEvent $event) {
    $variables = $event->getArgument('variables');
    $message = $this->processVariables($action->getSettings('message'), $variables);

    // We can't use dependency injection here because of the way business_rules.module
    // creates plugin instances.
    // See \Drupal\business_rules\Form\ItemForm.
    \Drupal::logger('apigee_actions')->log((int) $action->getSettings('level'), $message);
  }

}
