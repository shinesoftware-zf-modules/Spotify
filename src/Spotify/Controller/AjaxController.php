<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Spotify\Controller;

use Base\Service\SettingsService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Spotify\Service\SpotifyServiceInterface;
use Base\Service\SettingsServiceInterface;
use Base\Model\UrlRewrites as UrlRewrites;

class AjaxController extends AbstractActionController
{
	protected $spotifyProfile;
	protected $spotifyService;
	protected $form;
	protected $filter;
	protected $baseSettings;
	protected $translator;
	
	/**
	 * preDispatch spotify of the spotify
	 * 
	 * (non-PHPdoc)
	 * @see Zend\Mvc\Controller.AbstractActionController::onDispatch()
	 */
	public function onDispatch(\Zend\Mvc\MvcEvent $e){
		$this->translator = $e->getApplication()->getServiceManager()->get('translator');
		
		return parent::onDispatch( $e );
	}
	
	/**
	 * Constructor 
	 * @param \Spotify\Service\SpotifyProfileService $spotifyProfile
	 * @param \Base\Service\SettingsService $settingservice
	 */
	public function __construct(\Spotify\Service\SpotifyService $spotifyService, 
	        \Spotify\Service\SpotifyProfileService $spotifyProfile, 
	        \Spotify\Form\SpotifyForm $form,
	        \Spotify\Form\SpotifyFilter $filter,
	        \Base\Service\SettingsService $settingservice)
	{
		$this->spotifyService = $spotifyService;
		$this->spotifyProfile = $spotifyProfile;
		$this->form = $form;
		$this->filter = $filter;
		$this->baseSettings = $settingservice;
	}
	
	public function indexAction(){
	    die('test');
	}
	
	
	/**
	 * Get the prefered playlist tracks of the user
	 */
	public function tracksAction(){
	    $request = $this->getRequest();
	    $userId = $this->params()->fromRoute('id');
	    $result = array();
	    $result['list'] = $this->translator->translate('There is not any Spotify account set yet');
	    $serviceLocator = $this->getServiceLocator();
	    $config = $serviceLocator->get('Config');
	    try {
	        $session = new \SpotifyWebAPI\Session($config['SpotifyClient']['client_id'], $config['SpotifyClient']['client_secret'], $config['SpotifyClient']['redirect_uri']);
	    
            $spotify_user_id = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('user_id', $userId);
            $playlistId = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('playlist', $userId);
            $record = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('access_token', $userId);
            
	        if($record){
	            $api = new \SpotifyWebAPI\SpotifyWebAPI();
	    
	            $session->setRefreshToken($record->getValue());
	            $session->refreshToken();
	    
	            $accessToken = $session->getAccessToken();
	    
	            // Set the new access token on the API wrapper
	            $api->setAccessToken($accessToken);
	    
	            if($playlistId){
                    $playlist = $api->getUserPlaylist($spotify_user_id->getValue(), $playlistId->getValue());
                    $result['link'] = $playlist->external_urls->spotify;
                    $result['title'] = $playlist->name;
    	            $result['list'] = '<ul class="list-inline">';
    	                if($playlist->tracks->items){
    	                    foreach($playlist->tracks->items as $item){
    	                        if($playlist->owner->id == $spotify_user_id->getValue()) {
									if(!empty($item->track->external_urls->spotify)){
										$result['list'] .= '<li><a target="_blank" href="' . $item->track->external_urls->spotify . '"><img class="img-responsive img-thumbnail" alt="' . $item->track->name . '" title="' . $item->track->name .'(' . $item->track->artists[0]->name .')" src="' . $item->track->album->images[2]->url. '" width="64" /></a></li>';
									}
    	                        }
    	                    }
    	                }
    	            $result['list'] .= '</ul>';
	            }else{
	                $result['list'] = $this->translator->translate('There is no playlist preference set yet');
	            }
	        }
        }catch (\Exception $e){
            $result = $e->getMessage();
        }
	    die(json_encode($result));
	}
	
	
	/**
	 * Get the prefered playlist tracks of the user
	 */
	public function playlistracksAction(){
	    $request = $this->getRequest();
	    $userId = $this->params()->fromRoute('id');
	    $playlistId = $this->params()->fromRoute('playlist');
	    $result = array();
	    $result['list'] = $this->translator->translate('There is not any Spotify account set yet');
	    $serviceLocator = $this->getServiceLocator();
	    $config = $serviceLocator->get('Config');
	    try {
	        $session = new \SpotifyWebAPI\Session($config['SpotifyClient']['client_id'], $config['SpotifyClient']['client_secret'], $config['SpotifyClient']['redirect_uri']);
	    
	        // Get a random and already Spotify User AUTHORIZED from the database
            $spotifyUserID = $this->getServiceLocator()->get('SpotifyProfileService')->findByCode('user_id');
            if(!$spotifyUserID->getUserId()){
                $result['list'] = $this->translator->translate('There is not any Spotify user set in the database yet');
                die(json_encode($result));
            }
            
            $spotify_user_id = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('user_id', $spotifyUserID->getUserId());
            $record = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('access_token', $spotifyUserID->getUserId());
            
	        if($record){
	            $api = new \SpotifyWebAPI\SpotifyWebAPI();
	    
	            $session->setRefreshToken($record->getValue());
	            $session->refreshToken();
	    
	            $accessToken = $session->getAccessToken();
	    
	            // Set the new access token on the API wrapper
	            $api->setAccessToken($accessToken);
	    
	            if($playlistId){
                    $playlist = $api->getUserPlaylist($userId, $playlistId);
                    $result['link'] = $playlist->external_urls->spotify;
                    $result['title'] = $playlist->name;
    	            $result['list'] = '<h4><i class="fa fa-spotify"></i> <a target="_blank" href="'.$playlist->external_urls->spotify.'">'.$playlist->name.'</a></h4><ul class="list-inline">';
    	                if($playlist->tracks->items){
    	                    foreach($playlist->tracks->items as $item){
    	                        $result['list'] .= '<li><a target="_blank" href="' . $item->track->external_urls->spotify . '"><img class="img-responsive img-thumbnail" alt="' . $item->track->name . '" title="' . $item->track->name .'(' . $item->track->artists[0]->name .')" src="' . $item->track->album->images[2]->url. '" width="48" /></a></li>';
    	                    }
    	                }
    	            $result['list'] .= '</ul>';
	            }else{
	                $result['list'] = $this->translator->translate('There is no playlist preference set yet');
	            }
	        }
        }catch (\Exception $e){
            $result = $e->getMessage();
        }
	    die(json_encode($result));
	}
	
	/**
	 * Get the playlist of the user
	 */
	public function playlistsAction(){
	    $request = $this->getRequest();
	    $userId = $this->params()->fromRoute('id');
	    $result = $this->translator->translate('There is not any Spotify account set yet');
	    $serviceLocator = $this->getServiceLocator();
	    $config = $serviceLocator->get('Config');
	    try {
	        $session = new \SpotifyWebAPI\Session($config['SpotifyClient']['client_id'], $config['SpotifyClient']['client_secret'], $config['SpotifyClient']['redirect_uri']);
	    
	        $spotify_user_id = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('user_id', $userId);
	        $record = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('access_token', $userId);
	    
	        if($record){
	            $api = new \SpotifyWebAPI\SpotifyWebAPI();
	    
	            $session->setRefreshToken($record->getValue());
	            $session->refreshToken();
	    
	            $accessToken = $session->getAccessToken();
	    
	            // Set the new access token on the API wrapper
	            $api->setAccessToken($accessToken);
	    
	            if($spotify_user_id){
    	            $playlists = $api->getUserPlaylists($spotify_user_id->getValue());
    	            
    	            $result = '<ul class="list-inline">';
    	                if($playlists->items){
    	                    foreach($playlists->items as $playlist){
    	                        if($playlist->owner->id == $spotify_user_id->getValue()) {
    	                            $result .= '<li><a target="_blank" href="' . $playlist->external_urls->spotify . '">'. $playlist->name .' <span title="' . $this->translator->translate('Tracks') .'" class="badge badge-default">' . $playlist->tracks->total .'</span></a></li>';
    	                        }
    	                    }
    	                }
    	            $result .= '</ul>';
	            }
	        }
        }catch (\Exception $e){
            $result = $e->getMessage();
        }
	    
	    die($result);
	}
}