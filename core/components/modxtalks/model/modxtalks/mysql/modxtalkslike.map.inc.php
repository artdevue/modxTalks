<?php
$xpdo_meta_map['modxTalksLike'] = [
	'package' => 'modxTalks',
	'version' => '1.1',
	'table' => 'modxtalks_like',
	'extends' => 'xPDOSimpleObject',
	'fields' =>
		[
			'id' => null,
			'postId' => null,
			'memberId' => null,
		],
	'fieldMeta' =>
		[
			'id' =>
				[
					'dbtype' => 'int',
					'precision' => '11',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
					'index' => 'pk',
				],
			'postId' =>
				[
					'dbtype' => 'int',
					'precision' => '10',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
					'index' => 'pk',
				],
			'memberId' =>
				[
					'dbtype' => 'int',
					'precision' => '10',
					'attributes' => 'unsigned',
					'phptype' => 'integer',
					'null' => false,
					'index' => 'pk',
				],
		],
	'indexes' =>
		[
			'PRIMARY' =>
				[
					'alias' => 'PRIMARY',
					'primary' => true,
					'unique' => true,
					'type' => 'BTREE',
					'columns' =>
						[
							'postId' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
							'memberId' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
							'id' =>
								[
									'length' => '',
									'collation' => 'A',
									'null' => false,
								],
						],
				],
		],
];
