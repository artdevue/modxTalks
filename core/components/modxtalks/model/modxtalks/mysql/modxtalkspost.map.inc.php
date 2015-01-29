<?php
$xpdo_meta_map['modxTalksPost'] = array(
    'package' => 'modxTalks',
    'version' => '1.1',
    'table' => 'modxtalks_post',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array(
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
        ),
    'fieldMeta' =>
        array(
            'conversationId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'phptype' => 'integer',
                    'null' => false,
                    'index' => 'index',
                ),
            'idx' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'phptype' => 'integer',
                    'default' => 0,
                    'null' => false,
                    'index' => 'index',
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
            'time' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                ),
            'date' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '6',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                ),
            'editUserId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => true,
                ),
            'editTime' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => true,
                ),
            'deleteUserId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => true,
                ),
            'deleteTime' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => true,
                ),
            'hash' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '32',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'content' =>
                array(
                    'dbtype' => 'text',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'username' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '63',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'useremail' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '63',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'ip' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '16',
                    'phptype' => 'string',
                    'null' => false,
                    'defaul' => '0.0.0.0',
                ),
            'votes' =>
                array(
                    'dbtype' => 'mediumtext',
                    'phptype' => 'json',
                    'null' => true,
                ),
            'properties' =>
                array(
                    'dbtype' => 'mediumtext',
                    'phptype' => 'json',
                    'null' => true,
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
            'idx' =>
                array(
                    'alias' => 'idx',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' =>
                        array(
                            'idx' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                        ),
                ),
        ),
);
