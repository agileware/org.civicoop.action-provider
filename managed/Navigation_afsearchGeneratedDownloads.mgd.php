<?php
use CRM_ActionProvider_ExtensionUtil as E;

return [
  [
    'name' => 'Navigation_afsearchGeneratedDownloads',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('Generated Downloads'),
        'name' => 'afsearchGeneratedDownloads',
        'url' => 'civicrm/admin/action-provider-downloads',
        'icon' => 'crm-i fa-file-arrow-down',
        'permission' => [
          'administer CiviCRM system',
        ],
        'permission_operator' => 'AND',
        'parent_id.name' => 'Communications',
        'weight' => 13,
      ],
      'match' => ['name', 'domain_id'],
    ],
  ],
];
