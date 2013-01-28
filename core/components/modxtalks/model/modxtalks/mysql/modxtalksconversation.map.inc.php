<?php
$xpdo_meta_map['modxTalksConversation']= array (
  'package' => 'modxTalks',
  'version' => '1.1',
  'table' => 'modxtalks_conversation',
  'extends' => 'xPDOSimpleObject',
  'fields' =>
  array (
    'conversation' => NULL,
    'rid' => 0,
    'properties' => NULL,
  ),
  'fieldMeta' =>
  array (
    'conversation' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '63',
      'phptype' => 'string',
      'null' => false,
      'index' => 'index',
    ),
    'rid' =>
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'properties' =>
    array (
      'dbtype' => 'mediumtext',
      'phptype' => 'json',
      'null' => true,
    ),
  ),
  'indexes' =>
  array (
    'conversation' =>
    array (
      'alias' => 'conversation',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' =>
      array (
        'conversation' =>
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'rid' =>
    array (
      'alias' => 'rid',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' =>
      array (
        'rid' =>
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'composites' =>
  array (
    'Subscribers' =>
    array (
      'class' => 'modxTalksSubscribers',
      'local' => 'id',
      'foreign' => 'conversationId',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Comments' =>
    array (
      'class' => 'modxTalksPost',
      'local' => 'id',
      'foreign' => 'conversationId',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'UnconfirmedComments' =>
    array (
      'class' => 'modxTalksTempPost',
      'local' => 'id',
      'foreign' => 'conversationId',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
