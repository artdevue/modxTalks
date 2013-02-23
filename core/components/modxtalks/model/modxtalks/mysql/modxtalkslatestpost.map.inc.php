<?php
$xpdo_meta_map['modxTalksLatestPost']= array (
  'package' => 'modxTalks',
  'version' => '1.1',
  'table' => 'modxtalks_latest_post',
  'extends' => 'xPDOObject',
  'fields' =>
  array (
    'cid' => 0,
    'pid' => 0,
    'idx' => 0,
    'name' => NULL,
    'email' => NULL,
    'content' => '',
    'time' => NULL,
    'link' => NULL,
    'userId' => 0,
    'total' => 0,
    'title' => NULL,
  ),
  'fieldMeta' =>
  array (
    'cid' =>
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'pk',
    ),
    'pid' =>
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'default' => 0,
      'null' => false,
      'index' => 'index',
    ),
    'idx' =>
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'default' => 0,
      'null' => false,
      'index' => 'index',
    ),
    'userId' =>
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'time' =>
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
    ),
    'content' =>
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
    ),
    'link' =>
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
    ),
    'name' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '63',
      'phptype' => 'string',
      'null' => true,
    ),
    'email' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '63',
      'phptype' => 'string',
      'null' => true,
    ),
    'total' =>
    array (
      'dbtype' => 'int',
      'precision' => '3',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
    ),
    'title' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
    ),
  ),
  'indexes' => 
  array (
    'cid' => 
    array (
      'alias' => 'cid',
      'primary' => true,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'cid' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
