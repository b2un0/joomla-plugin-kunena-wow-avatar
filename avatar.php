<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class KunenaAvatarWoW_Avatar extends KunenaAvatar {
	protected $params = null;

	public function __construct($params) {
		$this->params = $params;
	}

	public function getEditURL() {
		return KunenaRoute::_('index.php?option=com_kunena&view=user&layout=edit');
	}

	protected function _getURL($user, $sizex, $sizey) {
		$user = KunenaFactory::getUser($user);
		
		$members = $this->_getMembers();
		
		if(!is_array($members)) {
			JFactory::getApplication()->enqueueMessage($members, 'error');
			return '';
		}
		
		foreach($members as $member) {
			if($user->name == $member->character->name) {
				return 'http://' . $this->params->get('region') . '.battle.net/static-render/' . $this->params->get('region') . '/' . $member->character->thumbnail;
			}
		}
		
		return '';
	}
	
	protected function _getMembers() {
		$url = 'http://' . $this->params->get('region') . '.battle.net/api/wow/guild/' . $this->params->get('realm') . '/' . $this->params->get('guild') . '?fields=members';
		
		$cache = JFactory::getCache(__CLASS__, 'output');
		$cache->setCaching(1);
		$cache->setLifeTime($this->params->get('cache_time', 60));
		 
		$key = md5($url);
		 
		if(!$result = $cache->get($key)) {
			try {
				$http = new JHttp(new JRegistry, new JHttpTransportCurl(new JRegistry));
				$http->setOption('userAgent', 'Joomla! ' . JVERSION . '; Kunena Avatar WoW Character; php/' . phpversion());
		
				$result = $http->get($url, null, $this->params->get('timeout', 10));
			}catch(Exception $e) {
				return $e->getMessage();
			}
		
			$cache->store($result, $key);
		}
		
		if($result->code != 200) {
			return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#'.$result->code, $result->code, array('target' => '_blank'));
		}
		
		$result->body = json_decode($result->body);
		
		return $result->body->members;		 
	}
}