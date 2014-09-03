<?php
$xpdo_meta_map['modxTalksLatestPost'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_latest_post',
	'extends' => 'xPDOObject',
	'fields' =>
		[
			'cid' => 0,
			'pid' => 0,
			'idx' => 0,
			'name' => null,
			'email' => null,
			'content' => '',
			'time' => null,
			'link' => null,
			'userId' => 0,
			'total' => 0,
			'title' => null,
		],
	'fieldMeta' =>
		[
			'cid' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'phptype' => 'integer',
					'null' => false,
					'default' => 0,
					'index' => 'pk',
				],
			'pid' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'phptype' => 'integer',
					'default' => 0,
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
			'content' =>
				[
					'dbtype' => 'text',
					'phptype' => 'string',
					'null' => false,
				],
			'link' =>
				[
					'dbtype' => 'text',
					'phptype' => 'string',
					'null' => false,
				],
			'name' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => true,
				],
			'email' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => true,
				],
			'total' =>
				[
					'dbtype' => 'int',
					'precision' => '3',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
				],
			'title' =>
				[
					'dbtype' => 'varchar',
					'precision' => '255',
					'phptype' => 'string',
					'null' => false,
				],
		],
	'indexes' =>
		[
			'cid' =>
				[
					'alias' => 'cid',
					'primary' => true,
					'unique' => true,
					'type' => 'BTREE',
					'columns' =>
						[
							'cid' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
						],
				],
		],
];
