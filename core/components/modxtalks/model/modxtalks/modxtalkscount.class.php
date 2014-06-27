<?php
/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package modxtalks
 *
 */

class modxTalksCount {
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config = array();

    function __construct(modX & $modx,array $config = array()) {
        $this->modx =& $modx;
        $this->config = array_merge(array(), $config);
    }

    /**
     * Plugin to run the page parser and display the number of comments on the resource.
     * @return boolean
     */
    public function mtcount() {
        // get a reference to the output
        $output =& $this->modx->resource->_output;
        // Choosing a pattern all of our values ​​from the resource
        if (preg_match_all ("/{%mt(.*?)%}/",$output , $mt_list)) {
            $r = array();
            $c = array();
            $lisrArray = $mt_list[1];
            array_walk($lisrArray, 'trim');
            // Divide into two arrays, one for the id of the resource, the other by conversation name
            foreach ($lisrArray as $key => $value){
                if ($value{0} == 'r') {
                    $r[$key] = substr($value, 2);
                } else {
                    $c[$key] = substr($value, 2);
                }
            }

            // If the array is not empty, choose the number of comments on the resource id (column rid)
            if ($r && is_array($r)) {
                array_walk($r, 'intval');
                $rr = $this->modx->newQuery('modxTalksConversation', array('rid:IN' => $r));
                if ($rr->prepare() && $rr->stmt->execute()) {
                    $results = $rr->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $resultr) {
                        $objMt = json_decode($resultr['modxTalksConversation_properties']);
                        if (in_array('r_'.$resultr['modxTalksConversation_rid'],$lisrArray)) {
                        $mt_list[1][array_search('r_'.$resultr['modxTalksConversation_rid'],$lisrArray)] =  isset($objMt->comments->total) ? $objMt->comments->total : 0;
                        }
                    }
                }

            }

            foreach ($r as $n) {
                $k = array_search('r_' . $n, $mt_list[1], true)
                if ($k !== false) {
                    $mt_list[1][$k] = 0;
                }
            }

            // If the array is not empty, choose the number of comments on the conversation name (column conversation)
            if ($c && is_array($c)) {
                $rr = $this->modx->newQuery('modxTalksConversation', array('conversation:IN' => $c));
                if ($rr->prepare() && $rr->stmt->execute()) {
                    $results = $rr->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $resultr) {
                        $objMt = json_decode($resultr['modxTalksConversation_properties']);
                        if (in_array('c_'.$resultr['modxTalksConversation_conversation'],$lisrArray)) {
                           $mt_list[1][array_search('c_'.$resultr['modxTalksConversation_conversation'],$lisrArray)] =  $objMt->comments->total;
                        }
                    }
                }

            }
            // Replace all your templates in the resource to the correct values
            $output = str_replace($mt_list[0], $mt_list[1], $output);
        }

        return true;
    }
}
