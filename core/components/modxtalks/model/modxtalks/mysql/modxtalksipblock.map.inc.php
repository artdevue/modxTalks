<?php
$xpdo_meta_map['modxTalksIpBlock'] = array(
    'package' => 'modxTalks',
    'version' => '1.1',
    'table' => 'modxtalks_ip_block',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array(
            'ip' => null,
            'date' => null,
            'intro' => null,
        ),
    'fieldMeta' =>
        array(
            'ip' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '255',
                    'phptype' => 'string',
                    'null' => false,
                    'index' => 'index',
                ),
            'date' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                    'index' => 'index',
                ),
            'intro' =>
                array(
                    'dbtype' => 'text',
                    'phptype' => 'string',
                    'null' => true,
                ),
        ),
    'indexes' =>
        array(
            'ip' =>
                array(
                    'alias' => 'ip',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' =>
                        array(
                            'ip' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                        ),
                ),
            'date' =>
                array(
                    'alias' => 'date',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' =>
                        array(
                            'date' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                        ),
                ),
        ),
    'validation' =>
        array(
            'rules' =>
                array(
                    'ip' =>
                        array(
                            'invalid' =>
                                array(
                                    'type' => 'preg_match',
                                    'rule' => '@^[0-9\.*]{3,15}$@',
                                    'message' => 'modxtalks.err_ip_adress',
                                ),
                        ),
                ),
        ),
);
