<?php
$xpdo_meta_map['modxTalksTempPost'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_temp_post',
	'extends' => 'xPDOSimpleObject',
	'fields' =>
		[
			'content' => null,
			'time' => null,
			'userId' => 0,
			'hash' => null,
			'token' => null,
			'username' => null,
			'useremail' => null,
			'conversationId' => null,
			'ip' => '0.0.0.0',
		],
	'fieldMeta' =>
		[
			'content' =>
				[
					'dbtype' => 'text',
					'phptype' => 'string',
					'null' => false,
				],
			'time' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
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
			'hash' =>
				[
					'dbtype' => 'varchar',
					'precision' => '32',
					'phptype' => 'string',
					'null' => true,
				],
			'token' =>
				[
					'dbtype' => 'varchar',
					'precision' => '32',
					'phptype' => 'string',
					'null' => false,
				],
			'username' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => false,
				],
			'useremail' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => false,
				],
			'conversationId' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
					'index' => 'index',
				],
			'ip' =>
				[
					'dbtype' => 'varchar',
					'precision' => '16',
					'phptype' => 'string',
					'default' => '0.0.0.0',
					'null' => false,
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
		],
];
