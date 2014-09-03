<?php
$xpdo_meta_map['modxTalksConversation'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_conversation',
	'extends' => 'xPDOSimpleObject',
	'fields' => [
		'title' => null,
		'conversation' => null,
		'rid' => 0,
		'total' => 0,
		'deleted' => 0,
		'unconfirmed' => 0,
		'properties' => null,
	],
	'fieldMeta' => [
		'title' => [
			'dbtype' => 'varchar',
			'precision' => '255',
			'phptype' => 'string',
			'null' => true,
		],
		'conversation' => [
			'dbtype' => 'varchar',
			'precision' => '63',
			'phptype' => 'string',
			'null' => false,
			'index' => 'index',
		],
		'rid' => [
			'dbtype' => 'int',
			'precision' => '11',
			'phptype' => 'integer',
			'null' => false,
			'default' => 0,
			'index' => 'index',
		],
		'total' => [
			'dbtype' => 'smallint',
			'precision' => '5',
			'phptype' => 'integer',
			'null' => false,
			'default' => 0,
		],
		'deleted' => [
			'dbtype' => 'smallint',
			'precision' => '5',
			'phptype' => 'integer',
			'null' => false,
			'default' => 0,
		],
		'unconfirmed' => [
			'dbtype' => 'smallint',
			'precision' => '5',
			'phptype' => 'integer',
			'null' => false,
			'default' => 0,
		],
		'properties' => [
			'dbtype' => 'mediumtext',
			'phptype' => 'json',
			'null' => true,
		],
	],
	'indexes' => [
		'conversation' => [
			'alias' => 'conversation',
			'primary' => false,
			'unique' => true,
			'type' => 'BTREE',
			'columns' => [
				'conversation' => [
					'length' => '',
					'collation' => 'A',
					'null' => false,
				],
			],
		],
		'rid' => [
			'alias' => 'rid',
			'primary' => false,
			'unique' => false,
			'type' => 'BTREE',
			'columns' => [
				'rid' => [
					'length' => '',
					'collation' => 'A',
					'null' => false,
				],
			],
		],
	],
	'composites' => [
		'Subscribers' => [
			'class' => 'modxTalksSubscribers',
			'local' => 'id',
			'foreign' => 'conversationId',
			'cardinality' => 'many',
			'owner' => 'local',
		],
		'Comments' => [
			'class' => 'modxTalksPost',
			'local' => 'id',
			'foreign' => 'conversationId',
			'cardinality' => 'many',
			'owner' => 'local',
		],
		'UnconfirmedComments' => [
			'class' => 'modxTalksTempPost',
			'local' => 'id',
			'foreign' => 'conversationId',
			'cardinality' => 'many',
			'owner' => 'local',
		],
	],
];
