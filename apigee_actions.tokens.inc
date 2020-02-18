<?php

/**
 * @file
 * Implements tokens for Apigee Edge entities.
 */

use Drupal\apigee_edge\Entity\EdgeEntityInterface;
use Drupal\apigee_edge\Entity\EdgeEntityTypeInterface;
use Drupal\apigee_edge\Entity\FieldableEdgeEntityInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info_alter().
 */
function apigee_actions_token_info_alter(&$info) {
  $entity_types = \Drupal::entityTypeManager()->getDefinitions();
  $type_info = \Drupal::service('plugin.manager.field.field_type')->getDefinitions();

  /** @var \Drupal\Core\Entity\EntityTypeInterface[] $apigee_entity_types */
  $apigee_entity_types = array_filter($entity_types, function (EntityTypeInterface $entity_type) {
    return $entity_type instanceof EdgeEntityTypeInterface;
  });

  foreach ($apigee_entity_types as $entity_type) {
    $token_type = $entity_type->get('token_type');

    if (!isset($info['types'][$token_type]) || !isset($info['tokens'][$token_type])) {
      $info['types'][$entity_type->id()] = [
        'name' => $entity_type->getLabel(),
        'needs-data' => $entity_type->id(),
        'description' => t('Tokens related to @name.', [
          '@name' => $entity_type->getPluralLabel(),
        ]),
        'module' => 'apigee_actions',
      ];

      if ($entity_type->entityClassImplements(FieldableEdgeEntityInterface::class)) {
        $fields = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type->id());
        foreach ($fields as $field_name => $field) {
          /** @var \Drupal\field\FieldStorageConfigInterface $field */

          $labels = _token_field_label($entity_type->id(), $field->getName());
          $label = array_shift($labels);
          $params['@type'] = $type_info[$field->getType()]['label'];
          if (!empty($labels)) {
            $params['%labels'] = implode(', ', $labels);
            $description = t('@type field. Also known as %labels.', $params);
          }
          else {
            $description = t('@type field.', $params);
          }

          $field_token_name = $token_type . '-' . $field_name;
          $info['tokens'][$token_type][$field_name] = [
            'name' => Html::escape($label),
            'description' => $description,
            'module' => 'token',
          ];
        }
      }
    }
  }
}

/**
 * Implements hook_tokens().
 */
function apigee_actions_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'entity' && !empty($data['entity_type']) && !empty($data['entity']) && !empty($data['token_type'])) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $data['entity'];

    if (!($entity instanceof EdgeEntityInterface)) {
      return $replacements;
    }

    foreach ($tokens as $field_name => $original) {
      // Ensure entity has requested field and is not empty.
      if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
        continue;
      }

      $replacements[$original] = $entity->get($field_name)->value;
    }
  }

  return $replacements;
}
