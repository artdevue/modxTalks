<?php
$xpdo_meta_map['modxTalksEmailBlock'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_email_block',
	'extends' => 'xPDOSimpleObject',
	'fields' =>
		[
			'email' => null,
			'date' => null,
			'intro' => null,
		],
	'fieldMeta' =>
		[
			'email' =>
				[
					'dbtype' => 'varchar',
					'precision' => '63',
					'phptype' => 'string',
					'null' => false,
					'index' => 'index',
				],
			'date' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
					'index' => 'index',
				],
			'intro' =>
				[
					'dbtype' => 'text',
					'phptype' => 'string',
					'null' => true,
				],
		],
	'indexes' =>
		[
			'email' =>
				[
					'alias' => 'email',
					'primary' => false,
					'unique' => true,
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
		],
	'validation' =>
		[
			'rules' =>
				[
					'ip' =>
						[
							'invalid' =>
								[
									'type' => 'preg_match',
									'rule' => '@^[0-9\.*]{3,15}$@',
									'message' => 'modxtalks.err_ip_adress',
								],
						],
				],
		],
];
