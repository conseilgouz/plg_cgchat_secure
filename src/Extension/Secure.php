<?php

/**
* CG Chat Secure Plugin  - Joomla 4.x/5.x plugin
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

namespace ConseilGouz\Plugin\Cgchat\Secure\Extension;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\Event\SubscriberInterface;
use ConseilGouz\CGSecure\Cgipcheck;

class Secure extends CMSPlugin implements SubscriberInterface
{
    public $myname = 'CGChatSecure';
    public $mymessage = '(CGChat) : try to access ...';
    public $errtype = 'w';	 // warning
    public $cgsecure_params;

    /**
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onCGChatStart' 	=> 'startCGChat',
            'onCGChatBeforeMsg' => 'beforeMsg',
            'onCGChatKill'      => 'goKill',
        ];
    }
    public function __construct(&$subject, $config)
    {
        $helperFile = JPATH_SITE . '/libraries/cgsecure/src/Cgipcheck.php';
        if (!is_file($helperFile)) {
            return;
        }
        parent::__construct($subject, $config);
        $this->cgsecure_params = Cgipcheck::getParams();
        $prefixe = $_SERVER['SERVER_NAME'];
        $prefixe = substr(str_replace('www.', '', $prefixe), 0, 2);
        $this->mymessage = $prefixe.$this->errtype.'-'.$this->mymessage;
    }
    /*
    *	onCGChatStart : Check IP on prepare Forms
    *
    *   @context  string   must contain com_cgchat.cgchat/com_cgchat.history
    *   @params   Registry contain com_cgchat parameters
    *   @response String   empty if OK, text if error
    *
    *	@return boolean    always true
    */

    public function startCGChat($event)
    {
        $lang = $this->getApplication()->getLanguage();
        $lang->load('plg_cgchat_secure', JPATH_ADMINISTRATOR);
        $helperFile = JPATH_SITE . '/libraries/cgsecure/src/Cgipcheck.php';
        if (!is_file($helperFile)) {
            $event->setArgument('response', Text::_('PLG_CGCHAT_SECURE_NEED'));
            return;
        }

        $context	= $event['context'];
        $params 	= $event['params'];
        $user 		= $event['user'];
        $txt		= $event['txt'];
        if (($context != 'com_cgchat.cgchat') && ($context != 'com_cgchat.history')) {
            $event->setArgument('response', "Erreur context");
        }

        if (Cgipcheck::check_spammer($this, $this->myname)) {
            $event->setArgument('response', Text::_('PLG_CGCHAT_SECURE_SPAMMER'));
        }
    }

    /*
    *	onCGChatBeforeMsg : before writing new message in DB
    *
    *   @context  string   must contain com_cgchat.cgchat
    *   @params   Registry contain com_cgchat parameters
    *	@user	  CGChatUser
    *	@txt	  String   text to display
    *   @response String   empty if OK, text if error
    *
    *	@return boolean    always true

    */
    public function beforeMsg($event)
    {
        $context	= $event['context'];
        $params 	= $event['params'];
        $user 		= $event['user'];
        $txt		= $event['txt'];
        if ($context != 'com_cgchat.cgchat') {
            $event->setArgument('response', "Erreur context");
        }

        return true;
    }
    /*
    *	onCGChatKill : before killing session
    *
    *   @context  string   must contain com_cgchat.cgchat
    *   @params   Registry contain com_cgchat parameters
    *	@session  String
    *   @response String   empty if OK, text if error
    *
    *	@return boolean    always true

    */
    public function goKill($event)
    {
        $context	= $event['context'];
        $params 	= $event['params'];
        $user 		= $event['user'];
        $txt		= $event['txt'];
        if ($context != 'com_cgchat.cgchat') {
            $event->setArgument('response', "Erreur context");
        }

        return true;
    }


}
