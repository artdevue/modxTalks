<?php
$xpdo_meta_map['modxTalksTempPost'] = array(
    'package' => 'modxTalks',
    'version' => '1.1',
    'table' => 'modxtalks_temp_post',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array(
            'content' => null,
            'time' => null,
            'userId' => 0,
            'hash' => null,
            'token' => null,
            'username' => null,
            'useremail' => null,
            'conversationId' => null,
            'ip' => '0.0.0.0',
        ),
    'fieldMeta' =>
        array(
            'content' =>
                array(
                    'dbtype' => 'text',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'time' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                ),
            'userId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                    'default' => 0,
                    'index' => 'index',
                ),
            'hash' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '32',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'token' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '32',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'username' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '63',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'useremail' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '63',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'conversationId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                    'index' => 'index',
                ),
            'ip' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '16',
                    'phptype' => 'string',
                    'default' => '0.0.0.0',
                    'null' => false,
                ),
        ),
    'indexes' =>
        array(
            'conversationId' =>
                array(
                    'alias' => 'conversationId',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' =>
                        array(
                            'conversationId' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                        ),
                ),
        ),
);
