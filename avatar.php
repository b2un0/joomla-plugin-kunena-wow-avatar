<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class KunenaAvatarWoW_Avatar extends KunenaAvatar
{

    protected $params = null;
    protected $default = 'media/kunena/avatars/nophoto.jpg';
    protected $character = null;

    public function __construct(&$params)
    {
        $this->params = $params;

        $this->params->set('guild', rawurlencode(JString::strtolower($this->params->get('guild'))));
        $this->params->set('realm', rawurlencode(JString::strtolower($this->params->get('realm'))));
        $this->params->set('region', JString::strtolower($this->params->get('region')));

        $this->default = $this->params->get('default', $this->default);
    }

    public function getLink($user, $class = '', $sizex = 90, $sizey = 90)
    {
        $size = $this->getSize($sizex, $sizey);
        $avatar = $this->getURL($user, $size->x, $size->y);

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
            if (JPluginHelper::isEnabled('system', 'darktip')) {
                $attributes['data-darktip'] = 'wow.character:' . $this->params->get('region') . '.' . $this->params->get('realm') . '.' . $this->character->name . '(' . $this->params->get('lang', 'en') . ')';
            }

            switch ($this->params->get('link', 'battle.net')) {
                case 'battle.net':
                    $url = 'http://' . $this->params->get('region') . '.battle.net/wow/' . $this->params->get('lang') . '/character/' . $this->params->get('realm') . '/' . $this->character->name . '/';
                    break;

                case 'wowhead.com':
                    $url = 'http://' . $this->params->get('lang') . '.wowhead.com/profile=' . $this->params->get('region') . '.' . $this->params->get('realm') . '.' . $this->character->name;
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

        $members = $this->getWoWCharacterList();

        if (!is_array($members)) {
            JFactory::getApplication()->enqueueMessage('Kunena - WOW Avatar: ' . $members, 'error');
            return $this->default;
        }

        $name = $user->{$this->params->get('mapping', 'name')};
        $name = JString::strtolower($name);

        $this->character = null;
        foreach ($members as $member) {
            $member->character->name = JString::strtolower($member->character->name);
            if ($name == $member->character->name) {
                $this->character = $member->character;
                return 'http://' . $this->params->get('region') . '.battle.net/static-render/' . $this->params->get('region') . '/' . $member->character->thumbnail;
            }
        }

        return $this->default;
    }

    protected function getWoWCharacterList()
    {
        $url = 'http://' . $this->params->get('region') . '.battle.net/api/wow/guild/' . $this->params->get('realm') . '/' . $this->params->get('guild') . '?fields=members';

        $cache = JFactory::getCache('wow', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($this->params->get('cache_time', 60));

        $key = md5($url);

        if (!$result = $cache->get($key)) {
            try {
                $http = JHttpFactory::getHttp();
                $http->setOption('userAgent', 'Joomla! ' . JVERSION . '; Kunena Avatar WoW Character; php/' . phpversion());

                $result = $http->get($url, null, $this->params->get('timeout', 10));
            } catch (Exception $e) {
                return $e->getMessage();
            }

            $cache->store($result, $key);
        }

        if ($result->code != 200) {
            return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#' . $result->code, $result->code, array('target' => '_blank'));
        }

        $result->body = json_decode($result->body);

        return $result->body->members;
    }
}