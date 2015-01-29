<?php
$xpdo_meta_map['modxTalksLike'] = array(
    'package' => 'modxTalks',
    'version' => '1.1',
    'table' => 'modxtalks_like',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array(
            'id' => null,
            'postId' => null,
            'memberId' => null,
        ),
    'fieldMeta' =>
        array(
            'id' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '11',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                    'index' => 'pk',
                ),
            'postId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '10',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                    'index' => 'pk',
                ),
            'memberId' =>
                array(
                    'dbtype' => 'int',
                    'precision' => '10',
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => false,
                    'index' => 'pk',
                ),
        ),
    'indexes' =>
        array(
            'PRIMARY' =>
                array(
                    'alias' => 'PRIMARY',
                    'primary' => true,
                    'unique' => true,
                    'type' => 'BTREE',
                    'columns' =>
                        array(
                            'postId' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                            'memberId' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                            'id' =>
                                array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => false,
                                ),
                        ),
                ),
        ),
);
