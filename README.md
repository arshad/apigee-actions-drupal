# Apigee Actions
[![CircleCI](https://circleci.com/gh/arshad/apigee-actions-drupal.svg?style=shield)](https://circleci.com/gh/arshad/apigee-actions-drupal)

The Apigee Actions module provides rules integration for Apigee Edge. It makes it easy to automate tasks and react on events such as:

  * Sending an email when an App is created.
  * Notify a developer when added to a Team.

## Events

The following events are supported out of the box:

### App
`\Drupal\apigee_edge\Entity\DeveloperApp`

| Event | Name  |
|---|---|
| After saving a new App  | `apigee_actions_entity_insert:developer_app`  |
| After deleting an App   | `apigee_actions_entity_delete:developer_app`  |
| After updating an App   | `apigee_actions_entity_insert:developer_app`  |

### Team App
`\Drupal\apigee_edge_teams\Entity\TeamApp`

| Event | Name  |
|---|---|
| After saving a new Team App  | `apigee_actions_entity_insert:team_app`  |
| After deleting an Team App   | `apigee_actions_entity_delete:team_app`  |
| After updating an Team App   | `apigee_actions_entity_insert:team_app`  |

### Team
`\Drupal\apigee_edge_teams\Entity\Team`

| Event | Name  |
|---|---|
| After saving a new Team  | `apigee_actions_entity_insert:team`  |
| After deleting an Team   | `apigee_actions_entity_delete:team`  |
| After updating an Team   | `apigee_actions_entity_insert:team`  |
| After adding a team member | `apigee_actions_entity_add_member:team`  |
| After removing a team member | `apigee_actions_entity_remove_member:team`  |

## Examples

The `apigee_actions_examples` module ships with some example rules you can use to test:

1. Log a message when team is deleted.
2. Notify developer when added to a team
3. Notify developer when adding a new app
4. Notify site admins when app is created
