<?php
$xpdo_meta_map['modxTalksSubscribers'] = array(
    'package' => 'modxTalks',
    'version' => '1.1',
    'table' => 'modxtalks_subscribers',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array(
            'conversationId' => '',
            'email' => '',
            'properties' => null,
        ),
    'fieldMeta' =>
        array(
            'conversationId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                ),
            'email' =>
                array(
                    'dbtype' => 'varchar',
                    'precision' => '63',
                    'phptype' => 'string',
                    'null' => false,
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
            'email' =>
                array(
                    'alias' => 'email',
                    'primary' => false,
                    'unique' => false,
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
