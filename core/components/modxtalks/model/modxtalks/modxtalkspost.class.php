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

class modxTalksPost extends xPDOSimpleObject {
    public function __construct(& $xpdo) {
        parent :: __construct($xpdo);
    }

    /**
     * Get user data
     *
     * @access public
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


    /**
     * Validate Email address
     *
     * @access public
     * @param string $email Email Address
     * @return boolean True if Email Address is correct
     */
    public function validateEmail($email) {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex+1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } else if (preg_match('/\\.\\./', $local)) {
                // local part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                // character not valid in domain part
                $isValid = false;
            } else if (preg_match('/\\.\\./', $domain)) {
                // domain part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
                // character not valid in local part unless
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
                    $isValid = false;
                }
            }

            // Filter out dispossable email adresses
            $spamDomain = array('0815.ru0clickemail.com', '0wnd.net', '0wnd.org', '10minutemail.com', '20minutemail.com', '2prong.com', '3d-painting.com', '4warding.com', '4warding.net', '4warding.org', '9ox.net', 'a-bc.net', 'amilegit.com', 'anonbox.net', 'anonymbox.com', 'antichef.com', 'antichef.net', 'antispam.de', 'baxomale.ht.cx', 'beefmilk.com', 'binkmail.com', 'bio-muesli.net', 'bobmail.info', 'bodhi.lawlita.com', 'bofthew.com', 'brefmail.com', 'bsnow.net', 'bugmenot.com', 'bumpymail.com', 'casualdx.com', 'chogmail.com', 'cool.fr.nf', 'correo.blogos.net', 'cosmorph.com', 'courriel.fr.nf', 'courrieltemporaire.com', 'curryworld.de', 'cust.in', 'dacoolest.com', 'dandikmail.com', 'deadaddress.com', 'despam.it', 'devnullmail.com', 'dfgh.net', 'digitalsanctuary.com', 'discardmail.com', 'discardmail.de', 'disposableaddress.com', 'disposemail.com', 'dispostable.com', 'dm.w3internet.co.uk', 'example.com', 'dodgeit.com', 'dodgit.com', 'dodgit.org', 'dontreg.com', 'dontsendmespam.de', 'dump-email.info', 'dumpyemail.com', 'e4ward.com', 'email60.com', 'emailias.com', 'emailinfive.com', 'emailmiser.com', 'emailtemporario.com.br', 'emailwarden.com', 'ephemail.net', 'explodemail.com', 'fakeinbox.com', 'fakeinformation.com', 'fastacura.com', 'filzmail.com', 'fizmail.com', 'frapmail.com', 'garliclife.com', 'get1mail.com', 'getonemail.com', 'getonemail.net', 'girlsundertheinfluence.com', 'gishpuppy.com', 'great-host.in', 'gsrv.co.uk', 'guerillamail.biz', 'guerillamail.com', 'guerillamail.net', 'guerillamail.org', 'guerrillamail.com', 'guerrillamailblock.com', 'haltospam.com', 'hotpop.com', 'ieatspam.eu', 'ieatspam.info', 'ihateyoualot.info', 'imails.info', 'inboxclean.com', 'inboxclean.org', 'incognitomail.com', 'incognitomail.net', 'ipoo.org', 'irish2me.com', 'jetable.com', 'jetable.fr.nf', 'jetable.net', 'jetable.org', 'junk1e.com', 'kaspop.com', 'kulturbetrieb.info', 'kurzepost.de', 'lifebyfood.com', 'link2mail.net', 'litedrop.com', 'lookugly.com', 'lopl.co.cc', 'lr78.com', 'maboard.com', 'mail.by', 'mail.mezimages.net', 'mail4trash.com', 'mailbidon.com', 'mailcatch.com', 'maileater.com', 'mailexpire.com', 'mailin8r.com', 'mailinator.com', 'mailinator.net', 'mailinator2.com', 'mailincubator.com', 'mailme.lv', 'mailnator.com', 'mailnull.com', 'mailzilla.org', 'mbx.cc', 'mega.zik.dj', 'meltmail.com', 'mierdamail.com', 'mintemail.com', 'moncourrier.fr.nf', 'monemail.fr.nf', 'monmail.fr.nf', 'mt2009.com', 'mx0.wwwnew.eu', 'mycleaninbox.net', 'mytrashmail.com', 'neverbox.com', 'nobulk.com', 'noclickemail.com', 'nogmailspam.info', 'nomail.xl.cx', 'nomail2me.com', 'no-spam.ws', 'nospam.ze.tc', 'nospam4.us', 'nospamfor.us', 'nowmymail.com', 'objectmail.com', 'obobbo.com', 'onewaymail.com', 'ordinaryamerican.net', 'owlpic.com', 'pookmail.com', 'proxymail.eu', 'punkass.com', 'putthisinyourspamdatabase.com', 'quickinbox.com', 'rcpt.at', 'recode.me', 'recursor.net', 'regbypass.comsafe-mail.net', 'safetymail.info', 'sandelf.de', 'saynotospams.com', 'selfdestructingmail.com', 'sendspamhere.com', 'shiftmail.com', '****mail.me', 'skeefmail.com', 'slopsbox.com', 'smellfear.com', 'snakemail.com', 'sneakemail.com', 'sofort-mail.de', 'sogetthis.com', 'soodonims.com', 'spam.la', 'spamavert.com', 'spambob.net', 'spambob.org', 'spambog.com', 'spambog.de', 'spambog.ru', 'spambox.info', 'spambox.us', 'spamcannon.com', 'spamcannon.net', 'spamcero.com', 'spamcorptastic.com', 'spamcowboy.com', 'spamcowboy.net', 'spamcowboy.org', 'spamday.com', 'spamex.com', 'spamfree24.com', 'spamfree24.de', 'spamfree24.eu', 'spamfree24.info', 'spamfree24.net', 'spamfree24.org', 'spamgourmet.com', 'spamgourmet.net', 'spamgourmet.org', 'spamherelots.com', 'spamhereplease.com', 'spamhole.com', 'spamify.com', 'spaminator.de', 'spamkill.info', 'spaml.com', 'spaml.de', 'spammotel.com', 'spamobox.com', 'spamspot.com', 'spamthis.co.uk', 'spamthisplease.com', 'speed.1s.fr', 'suremail.info', 'tempalias.com', 'tempemail.biz', 'tempemail.com', 'tempe-mail.com', 'tempemail.net', 'tempinbox.co.uk', 'tempinbox.com', 'tempomail.fr', 'temporaryemail.net', 'temporaryinbox.com', 'thankyou2010.com', 'thisisnotmyrealemail.com', 'throwawayemailaddress.com', 'tilien.com', 'tmailinator.com', 'tradermail.info', 'trash2009.com', 'trash-amil.com', 'trashmail.at', 'trash-mail.at', 'trashmail.com', 'trash-mail.com', 'trash-mail.de', 'trashmail.me', 'trashmail.net', 'trashymail.com', 'trashymail.net', 'tyldd.com', 'uggsrock.com', 'wegwerfmail.de', 'wegwerfmail.net', 'wegwerfmail.org', 'wh4f.org', 'whyspam.me', 'willselfdestruct.com', 'winemaven.info', 'wronghead.com', 'wuzupmail.net', 'xoxy.net', 'yogamaven.com', 'yopmail.com', 'yopmail.fr', 'yopmail.net', 'yuurok.com', 'zippymail.info', 'jnxjn.com', 'trashmailer.com', 'klzlk.com');

            if (in_array($domain, $spamDomain)) {
                $isValid = false;
            }
            if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
        return $isValid;
    }

    /**
     * Get a namespaced property for the Comment
     * @param string $key
     * @param string $namespace
     * @param null $default
     * @return null
     */
    public function getProperty($key, $namespace = 'value', $default = null) {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        return array_key_exists($namespace,$properties) && array_key_exists($key,$properties[$namespace]) ? $properties[$namespace][$key] : $default;
    }

    /**
     * Get the properties for the specific namespace for the Comment
     * @param string $namespace
     * @return array
     */
    public function getProperties($namespace = 'value') {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        return array_key_exists($namespace,$properties) ? $properties[$namespace] : array();
    }

    /**
     * Set a namespaced property for the Comment
     * @param string $key
     * @param mixed $value
     * @param string $namespace
     * @return bool
     */
    public function setProperty($key, $value, $namespace = 'value') {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        if (!array_key_exists($namespace,$properties)) $properties[$namespace] = array();
        $properties[$namespace][$key] = $value;
        return $this->set('properties',$properties);
    }

    /**
     * Set properties for a namespace on the Comment, optionally merging them with existing ones.
     * @param array $newProperties
     * @param string $namespace
     * @param bool $merge
     * @return boolean
     */
    public function setProperties(array $newProperties, $namespace = 'value', $merge = true) {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        if (!array_key_exists($namespace,$properties)) $properties[$namespace] = array();
        $properties[$namespace] = $merge ? array_merge($properties[$namespace],$newProperties) : $newProperties;
        return $this->set('properties',$properties);
    }

    /**
     * Add vote to Comment
     * @param mixed $value
     * @return bool
     */
    public function addVote($userId) {
        $users = 'users';
        $total = 'votes';
        $votes = $this->get('votes');
        $votes = !empty($votes) ? $votes : array($users => array(), $total => 0);
        if (!array_key_exists($users,$votes)) $votes[$users] = array();
        $votes[$users][] = (int) $userId;
        $votes[$total] = count($votes[$users]);
        return $this->set('votes',$votes);
    }

    /**
     * Remove vote from Comment
     * @param mixed $value
     * @return bool
     */
    public function removeVote($userId) {
        $users = 'users';
        $total = 'votes';
        $votes = $this->get('votes');
        $votes = !empty($votes) ? $votes : array($users => array(), $total => 0);
        if (!array_key_exists($users,$votes)) $votes[$users] = array();
        if (in_array($userId, $votes[$users])) {
            $key = array_search($userId, $votes[$users]);
            unset($votes[$users][$key]);
        }
        $votes[$total] = count($votes[$users]);
        return $this->set('votes',$votes);
    }

    /**
     * Get comment votes
     * @return array
     */
    public function getVotes() {
        $users = 'users';
        $total = 'votes';
        $votes = $this->get('votes');
        $votes = !empty($votes) ? $votes : array($users => array(), $total => 0);
        if (!array_key_exists($users,$votes)) $votes[$users] = array();
        return $votes;
    }

}