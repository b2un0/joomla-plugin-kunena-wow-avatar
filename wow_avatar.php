<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class plgKunenaWoW_Avatar extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        if (!(class_exists('KunenaForum') && KunenaForum::isCompatible('2.0') && KunenaForum::installed())) return;

        parent::__construct($subject, $config);
    }

    public function onKunenaGetAvatar()
    {
        require_once __DIR__ . '/avatar.php';
        return new KunenaAvatarWoW_Avatar($this->params);
    }
}