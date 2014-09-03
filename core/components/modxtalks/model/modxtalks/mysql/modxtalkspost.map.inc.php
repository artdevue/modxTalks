<?php
$xpdo_meta_map['modxTalksPost'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_post',
	'extends' => 'xPDOSimpleObject',
	'fields' =>
		[
			'conversationId' => null,
			'idx' => 0,
			'userId' => 0,
			'time' => null,
			'date' => null,
			'editUserId' => null,
			'editTime' => null,
			'deleteUserId' => null,
			'deleteTime' => null,
			'hash' => '',
			'content' => '',
			'username' => null,
			'useremail' => null,
			'ip' => '0.0.0.0',
			'votes' => null,
			'properties' => null,
		],
	'fieldMeta' =>
		[
			'conversationId' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'phptype' => 'integer',
					'null' => false,
					'index' => 'index',
				],
			'idx' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'phptype' => 'integer',
					'default' => 0,
					'null' => false,
					'index' => 'index',
				],
			'userId' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
					'default' => 0,
					'index' => 'index',
				],
			'time' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
				],
			'date' =>
				[
					'dbtype' => 'int',
					'precision' => '6',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
				],
			'editUserId' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => true,
				],
			'editTime' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => true,
				],
			'deleteUserId' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => true,
				],
			'deleteTime' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => true,
				],
			'hash' =>
				[
					'dbtype' => 'varchar',
					'precision' => '32',
					'phptype' => 'string',
					'null' => false,
				],
			'content' =>
				[
					'dbtype' => 'text',
					'phptype' => 'string',
					'null' => false,
				],
			'username' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => true,
				],
			'useremail' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => true,
				],
			'ip' =>
				[
					'dbtype' => 'varchar',
					'precision' => '16',
					'phptype' => 'string',
					'null' => false,
					'defaul' => '0.0.0.0',
				],
			'votes' =>
				[
					'dbtype' => 'mediumtext',
					'phptype' => 'json',
					'null' => true,
				],
			'properties' =>
				[
					'dbtype' => 'mediumtext',
					'phptype' => 'json',
					'null' => true,
				],
		],
	'indexes' =>
		[
			'conversationId' =>
				[
					'alias' => 'conversationId',
					'primary' => false,
					'unique' => false,
					'type' => 'BTREE',
					'columns' =>
						[
							'conversationId' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
						],
				],
			'date' =>
				[
					'alias' => 'date',
					'primary' => false,
					'unique' => false,
					'type' => 'BTREE',
					'columns' =>
						[
							'date' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
						],
				],
			'idx' =>
				[
					'alias' => 'idx',
					'primary' => false,
					'unique' => false,
					'type' => 'BTREE',
					'columns' =>
						[
							'idx' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
						],
				],
		],
];
