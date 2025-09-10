<?php
use CRM_ActionProvider_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Generated Downloads'),
  'icon' => 'fa-file-arrow-down',
  'server_route' => 'civicrm/admin/action-provider-downloads',
  'permission' => [
    'administer CiviCRM system',
  ],
];
