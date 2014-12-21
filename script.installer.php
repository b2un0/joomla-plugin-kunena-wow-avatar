<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class plgKunenaWow_AvatarInstallerScript
{
    public function preflight()
    {
        if (!class_exists('WoW')) {
            $link = JHtml::_('link', 'http://www.z-index.net', 'z-index.net', array('target' => '_blank'));
            JFactory::getApplication()->enqueueMessage(sprintf('You need the latest Joomla WoW configuration Extension from ', $link), 'error');
            return false;
        }
        return true;
    }
}