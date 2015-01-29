<?php
$xpdo_meta_map['modxTalksEmailBlock'] = array(
    'package' => 'modxTalks',
    'version' => '1.1',
    'table' => 'modxtalks_email_block',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array(
            'email' => null,
            'date' => null,
            'intro' => null,
        ),
    'fieldMeta' =>
        array(
            'email' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '63',
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
            'email' =>
                array(
                    'alias' => 'email',
                    'primary' => false,
                    'unique' => true,
                    'type' => 'BTREE',
                    'columns' =>
                        array(
                            'email' =>
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
