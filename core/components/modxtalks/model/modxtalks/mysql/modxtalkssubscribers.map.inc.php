<?php
$xpdo_meta_map['modxTalksSubscribers'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_subscribers',
	'extends' => 'xPDOSimpleObject',
	'fields' =>
		[
			'conversationId' => '',
			'email' => '',
			'properties' => null,
		],
	'fieldMeta' =>
		[
			'conversationId' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
				],
			'email' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => false,
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
			'email' =>
				[
					'alias' => 'email',
					'primary' => false,
					'unique' => false,
					'type' => 'BTREE',
					'columns' =>
						[
							'email' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
						],
				],
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
