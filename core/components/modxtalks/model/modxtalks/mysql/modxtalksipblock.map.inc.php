<?php
$xpdo_meta_map['modxTalksIpBlock'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_ip_block',
	'extends' => 'xPDOSimpleObject',
	'fields' =>
		[
			'ip' => null,
			'date' => null,
			'intro' => null,
		],
	'fieldMeta' =>
		[
			'ip' =>
				[
					'dbtype' => 'varchar',
					'precision' => '255',
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
			'ip' =>
				[
					'alias' => 'ip',
					'primary' => false,
					'unique' => false,
					'type' => 'BTREE',
					'columns' =>
						[
							'ip' =>
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
