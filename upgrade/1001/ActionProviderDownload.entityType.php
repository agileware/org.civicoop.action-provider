<?php
use CRM_ActionProvider_ExtensionUtil as E;

return [
  'name' => 'ActionProviderDownload',
  'table' => 'civicrm_action_provider_download',
  'class' => 'CRM_ActionProvider_DAO_ActionProviderDownload',
  'getInfo' => fn() => [
    'title' => E::ts('Action Provider Download'),
    'title_plural' => E::ts('Action Provider Downloads'),
    'description' => E::ts('Generated files in the action provider'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ActionProviderDownload ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('Contact who generated the file'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'created_date' => [
      'title' => E::ts('Created Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'readonly' => TRUE,
      'description' => E::ts('When was the file created.'),
      'default' => NULL,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
        'label' => E::ts('Created Date'),
      ],
    ],
    'expire_date' => [
      'title' => E::ts('Expire Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'readonly' => TRUE,
      'description' => E::ts('When will the file expire.'),
      'default' => NULL,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
        'label' => E::ts('Expire Date'),
      ],
    ],
    'download_name' => [
      'title' => E::ts('Download name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Name of the Download'),
      'required' => false,
      'default' => '',
    ],
    'file_name' => [
      'title' => E::ts('File name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('File name on the server'),
      'required' => false,
      'default' => '',
    ],
    'sub_dir' => [
      'title' => E::ts('Directory'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Directory on the server'),
      'required' => false,
      'default' => '',
    ],
  ],
  'getIndices' => fn() => [],
  'getPaths' => fn() => [],
];
