<?php
use CRM_ActionProvider_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_Generated_Downloads',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Generated_Downloads',
        'label' => E::ts('Generated Downloads'),
        'api_entity' => 'ActionProviderDownload',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'contact_id',
            'contact_id.display_name',
            'download_name',
            'created_date',
            'expire_date',
            'sub_dir',
            'file_name',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'SavedSearch_Generated_Downloads_SearchDisplay_Generated_Downloads',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Generated_Downloads',
        'label' => E::ts('Generated Downloads'),
        'saved_search_id.name' => 'Generated_Downloads',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(NULL),
          'sort' => [
            ['created_date', 'DESC'],
          ],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'created_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Created'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Contact'),
              'sortable' => TRUE,
              'link' => [
                'path' => '',
                'entity' => 'Contact',
                'action' => 'view',
                'join' => 'contact_id',
                'target' => '',
                'task' => '',
              ],
              'title' => E::ts(NULL),
            ],
            [
              'type' => 'field',
              'key' => 'download_name',
              'dataType' => 'String',
              'label' => E::ts('File'),
              'sortable' => TRUE,
              'link' => [
                'path' => 'civicrm/actionprovider/downloadfile?subdir=[sub_dir]&filename=[file_name]&downloadname=[download_name]',
                'entity' => '',
                'action' => '',
                'join' => '',
                'target' => '',
                'task' => '',
              ],
              'title' => E::ts(NULL),
            ],
            [
              'type' => 'field',
              'key' => 'expire_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Expire Date'),
              'sortable' => TRUE,
            ],
            [
              "links" => [[
                "path" => "",
                "icon" => "fa-trash",
                "text" => E::ts("Delete"),
                "style" => "danger",
                "conditions" => [],
                "task" => "delete",
                "entity" => "ActionProviderDownload",
                "action" => "",
                "join" => "",
                "target" => "crm-popup"
              ]],
              "type" => "links",
              "alignment" => "text-right"
            ]
          ],
          'actions' => TRUE,
          'classes' => ['table', 'table-striped'],
          'actions_display_mode' => 'menu',
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
