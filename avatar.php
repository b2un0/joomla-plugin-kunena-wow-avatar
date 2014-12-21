<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class KunenaAvatarWoW_Avatar extends KunenaAvatar
{
    protected static $error = false;

    protected $params = null;
    protected $default = 'media/kunena/avatars/nophoto.jpg';
    protected $character = null;

    public function __construct(&$params)
    {
        $this->params = $params;

        $this->default = $this->params->get('default', $this->default);
    }

    public function getLink($user, $class = '', $sizex = 90, $sizey = 90)
    {
        $size = $this->getSize($sizex, $sizey);
        $avatar = $this->getURL($user, $size->x, $size->y);
        $wow = WoW::getInstance();

        if (!$avatar) {
            return;
        }

        if ($class) {
            $attributes['class'] = $class;
        }

        if (!$this->resize) {
            $styles[] = 'max-width:' . $size->x . 'px';
            $styles[] = 'max-height:' . $size->y . 'px';
        }

        if (is_object($this->character)) {
            $this->character->realm = $this->realmUrlSafe($this->character->realm);

            if (JPluginHelper::isEnabled('system', 'darktip')) {
                $attributes['data-darktip'] = 'wow.character:' . $wow->params->get('region') . '.' . $this->character->realm . '.' . $this->character->name . '(' . $wow->params->get('locale', 'en') . ')';
            }

            switch ($wow->params->get('link')) {
                default:
                case 'battle.net':
                    $url = 'http://' . $wow->params->get('region') . '.battle.net/wow/' . $wow->params->get('locale') . '/character/' . $this->character->realm . '/' . $this->character->name . '/';
                    break;

                case 'wowhead.com':
                    $url = 'http://' . $wow->params->get('locale') . '.wowhead.com/profile=' . $wow->params->get('region') . '.' . $this->character->realm . '.' . $this->character->name;
                    break;
            }
        }

        if (isset($styles)) {
            $attributes['style'] = implode(';', $styles);
        }

        $link = JHtml::_('image', $avatar, JText::sprintf('COM_KUNENA_LIB_AVATAR_TITLE', $user->getName()), $attributes);

        // replace Avatar with Link on Profile view
        if ($class == 'kavatar' && isset($url)) {
            $link = JHtml::_('link', $url, $link, array('target' => '_blank'));
        }

        return $link;
    }

    protected function _getURL($user, $sizex, $sizey)
    {
        $user = KunenaFactory::getUser($user);
        $wow = WoW::getInstance();

        try {
            $result = $wow->getAdapter('WoWAPI')->getData('members');
        } catch (Exception $e) {
            return $e->getMessage();
        }

        if (!is_array($result->body->members)) {
            return $this->default;
        }

        $name = $user->{$this->params->get('mapping', 'name')};
        $name = JString::strtolower($name);

        $this->character = null;
        foreach ($result->body->members as $member) {
            $member->character->name = JString::strtolower($member->character->name);
            if ($name == $member->character->name) {
                $this->character = $member->character;
                return 'http://' . $wow->params->get('region') . '.battle.net/static-render/' . $wow->params->get('region') . '/' . $member->character->thumbnail;
            }
        }

        return $this->default;
    }

    protected function realmUrlSafe($realm)
    {
        if (version_compare(JVERSION, 3, '>=')) {
            return rawurlencode(JString::strtolower($realm));
        } else {
            return str_replace(array('%20', ' '), '-', $realm);
        }
    }
}