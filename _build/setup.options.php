<?php
/**
 * Build the setup options form.
 *
 * @package modxtalks
 * @subpackage build
 */
/* set some default values */
$values = array(
    'emailsFrom' => $modx->getOption('emailsender'),
    'emailsReplyTo' => $modx->getOption('emailsender'),
);
/* get values based on mode */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        $setting = $modx->getObject('modSystemSetting', array(
            'key' => 'modxtalks.emailsFrom'
        ));

        if ($setting != null) {
            $values['emailsFrom'] = $setting->get('value');
        }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting', array(
            'key' => 'modxtalks.emailsReplyTo'
        ));

        if ($setting != null) {
            $values['emailsReplyTo'] = $setting->get('value');
        }
        unset($setting);
        break;
    case xPDOTransport::ACTION_UPGRADE:
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

$output = '<label for="quip-emailsFrom">Emails From:</label>
<input type="text" name="emailsFrom" id="quip-emailsFrom" width="300" value="' . $values['emailsFrom'] . '" />
<br /><br />

<label for="quip-emailsReplyTo">Emails Reply-To:</label>
<input type="text" name="emailsReplyTo" id="quip-emailsReplyTo" width="300" value="' . $values['emailsReplyTo'] . '" />';

return $output;
