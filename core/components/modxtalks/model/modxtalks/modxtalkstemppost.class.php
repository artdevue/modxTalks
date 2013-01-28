<?php
/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <npobolka@gmail.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package modxtalks
 *
 */

class modxTalksTempPost extends xPDOSimpleObject {
    public function __construct(& $xpdo) {
        parent :: __construct($xpdo);
    }

    /**
     * Get user data
     *
     * @return array User name and email
     */
    public function getUserData() {
        $name = $this->username;
        $email = $this->useremail;
        /**
         * If this is registered user
         */
        if ($this->userId > 0) {
            if ($user = $this->xpdo->getObject('modUser',$this->userId)) {
                $profile = $user->getOne('Profile');
                $name = $user->get('username');
                if ($profile) {
                    $fullname = $profile->get('fullname');
                    $email = $profile->get('email');
                    $name = !empty($fullname) ? $fullname : $name;
                }
            }
        }

        return array('name' => $name, 'email' => $email);
    }
}