<?php
/**
* Copyright (c) 2014 Shine Software.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
*
* * Redistributions of source code must retain the above copyright
* notice, this list of conditions and the following disclaimer.
*
* * Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in
* the documentation and/or other materials provided with the
* distribution.
*
* * Neither the names of the copyright holders nor the names of the
* contributors may be used to endorse or promote products derived
* from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
* FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
* COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
* ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* @package Spotify
* @subpackage Service
* @author Michelangelo Turillo <mturillo@shinesoftware.com>
* @copyright 2014 Michelangelo Turillo.
* @license http://www.opensource.org/licenses/bsd-license.php BSD License 
* @link http://shinesoftware.com
* @version @@PACKAGE_VERSION@@
*/

namespace Spotify\Service;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Spotify;

class SpotifyService implements SpotifyServiceInterface, EventManagerAwareInterface
{
	protected $eventManager; 
	protected $spotifyProfile; 
	protected $spotifiApi; 
	protected $translator;
	
	public function __construct(\SpotifyWebAPI\SpotifyWebAPI $spotifiApi, \Spotify\Service\SpotifyProfileService $spotifyProfile, \Zend\Mvc\I18n\Translator $translator ){
	    $this->spotifiApi = $spotifiApi;
	    $this->translator = $translator;
	    $this->spotifyProfile = $spotifyProfile;
	}
	
	private function connect(){
	    
	}
	
	public function getList($userId){
	    $myPlaylist = array();
	    
	    try {
    	    $spotify_user_id = $this->spotifyProfile->findByCodeAndUserId('user_id', $userId);
    	    if($spotify_user_id){
    	        $playlists = $this->spotifiApi->getUserPlaylists($spotify_user_id->getValue());
    	        foreach ($playlists->items as $playlist){
    	            if($playlist->owner->id == $spotify_user_id->getValue()){
        	            $myPlaylist[$playlist->id] = $playlist->name;
    	            }
    	        }
    	    }
	    }catch (\Exception $e){
	        echo $e->getMessage();
	    }
	    return $myPlaylist;
	}
		
	/* (non-PHPdoc)
	 * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
	*/
	public function setEventManager (EventManagerInterface $eventManager){
	    $eventManager->addIdentifiers(get_called_class());
	    $this->eventManager = $eventManager;
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\EventManager\ProfileCapableInterface::getEventManager()
	*/
	public function getEventManager (){
	    if (null === $this->eventManager) {
	        $this->setEventManager(new EventManager());
	    }
	
	    return $this->eventManager;
	}
}