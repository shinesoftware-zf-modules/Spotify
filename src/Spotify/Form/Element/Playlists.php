<?php
namespace Spotify\Form\Element;

use Spotify\Service\SpotifyService;
use Zend\Form\Element\Select;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\I18n\Translator\Translator;

class Playlists extends Select implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $spotify;
    
    public function __construct(SpotifyService $spotify){
        parent::__construct();
        $this->spotify = $spotify;
    }
    
    public function init()
    {
        $data = array();
        
        if(!is_null($this->getServiceLocator()->getServiceLocator()->get('zfcuser_auth_service')->getIdentity())){
            $userId = $this->getServiceLocator()->getServiceLocator()->get('zfcuser_auth_service')->getIdentity()->getId();
            $playlists = $this->spotify->getList($userId);
            if(is_array($playlists)){
                foreach ($playlists as $key => $value){
                    $data[$key] = $value;
                }
            }
        }
        
        $this->setValueOptions($data);
    }
    
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
    }
    
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
