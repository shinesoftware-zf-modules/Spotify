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
* @subpackage Entity
* @author Michelangelo Turillo <mturillo@shinesoftware.com>
* @copyright 2014 Michelangelo Turillo.
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://shinesoftware.com
* @version @@PACKAGE_VERSION@@
*/


namespace Spotify;

use Base\View\Helper\Datetime;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Spotify\Service\SpotifyService;
use Spotify\Entity\SpotifyProfiles;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements DependencyIndicatorInterface{
	
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sm = $e->getApplication()->getServiceManager();
        $headLink = $sm->get('viewhelpermanager')->get('headLink');
//         $headLink->appendStylesheet('/css/Spotify/Spotify.css');
        
        $inlineScript = $sm->get('viewhelpermanager')->get('inlineScript');
//         $inlineScript->appendFile('/js/spotify/request.js');
        
    }
    
    /**
     * Check the dependency of the module
     * (non-PHPdoc)
     * @see Zend\ModuleManager\Feature.DependencyIndicatorInterface::getModuleDependencies()
     */
    public function getModuleDependencies()
    {
    	return array('Base', 'ZfcUser', 'Events');
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    
    /**
     * Set the Services Manager items
     */
    public function getServiceConfig ()
    { 
    	return array(
    			'factories' => array(
    			        
    			        'SpotifyProfileService' => function  ($sm)
    			        {
    			            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    			            $translator = $sm->get('translator');
    			            $resultSetPrototype = new ResultSet();
    			            $resultSetPrototype->setArrayObjectPrototype(new \Spotify\Entity\SpotifyProfiles());
    			            $tableGateway = new TableGateway('spotify_profiles', $dbAdapter, null, $resultSetPrototype);
    			            $service = new \Spotify\Service\SpotifyProfileService($tableGateway, $translator);
    			            return $service;
    			        },
    			        
    					'SpotifyService' => function  ($sm)
    					{
    					    $config = $sm->get('config');
    					    if(!empty($config['SpotifyClient'])){
    					        $config = $sm->get('config');
    					        
    					        if(!empty($config['SpotifyClient'])){
    					        
    					            $session = new \SpotifyWebAPI\Session($config['SpotifyClient']['client_id'], $config['SpotifyClient']['client_secret'], $config['SpotifyClient']['redirect_uri']);
                                    $api = new \SpotifyWebAPI\SpotifyWebAPI();

                                    // Getting the User connected ID
                                    $auth = $sm->get('zfcuser_auth_service');

                                    if(!is_null($auth->getIdentity())){
                                        $userId = $auth->getIdentity()->getId();
                                         
                                        $record = $sm->get('SpotifyProfileService')->findByCodeAndUserId('access_token', $userId);

                                        if($record){

                                            try {

                                                $session->setRefreshToken($record->getValue());
                                                $session->refreshToken();
                                                $accessToken = $session->getAccessToken();

                                                // Set the new access token on the API wrapper
                                                $api->setAccessToken($accessToken);
                                            }catch (\Exception $e) {
                                                #throw new \Exception('Spotify error: ' . $e->getMessage());
                                            }
                                        }
                                    }
                                    
    					        }else{
    					            throw new \Exception('No spotify.local.php file has been found!');
    					        }					        
    					        
        						$service = new \Spotify\Service\SpotifyService($api, $sm->get('SpotifyProfileService'), $sm->get('translator'));
        						return $service;
    					    }else{
					            throw new \Exception('No spotify.local.php file has been found!');
					        }	
    					},
    					
    					'SpotifyForm' => function  ($sm)
    					{
    					    $form = new \Spotify\Form\SpotifyForm();
    					    $form->setInputFilter($sm->get('SpotifyFilter'));
    					    return $form;
    					},
    					'SpotifyFilter' => function  ($sm)
    					{
    					    return new \Spotify\Form\SpotifyFilter();
    					},
    				),
    			);
    }
    
    
    /**
     * Get the form elements
     */
    public function getFormElementConfig ()
    {
        return array (
                'factories' => array (
                        'Spotify\Form\Element\Playlists' => function  ($sm)
                        {
                            $serviceLocator = $sm->getServiceLocator();
                            $service = $serviceLocator->get('SpotifyService');
                            $element = new \Spotify\Form\Element\Playlists($service);
                            return $element;
                        },
                     )
                );
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
