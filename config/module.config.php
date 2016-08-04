<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonspotify for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
        'asset_manager' => array(
                'resolver_configs' => array(
                        'collections' => array(
                                'js/application.js' => array(
                                        'js/readmore.min.js',
                                ),
                        ),
                        'paths' => array(
                                __DIR__ . '/../public',
                        ),
                ),
        ),
		'bjyauthorize' => array(
				'guards' => array(
					'BjyAuthorize\Guard\Route' => array(
							
		                // Generic route guards
		                array('route' => 'spotify', 'roles' => array('user')),
		                array('route' => 'spotify/default', 'roles' => array('user')),
		                array('route' => 'spotify/auth', 'roles' => array('user')),
		                array('route' => 'spotify/callback', 'roles' => array('user')),
		                array('route' => 'spotify/save', 'roles' => array('user')),
		                array('route' => 'spotify/playlist', 'roles' => array('user')),
		                array('route' => 'spotify/ajax', 'roles' => array('guest')),

					),
			  ),
		),
		'navigation' => array(
				'admin' => array(
        				
				),
		),
    'router' => array(
        'routes' => array(
            'spotify' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/spotify',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Spotify\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                        'page'			=> 1
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    
                    'auth' => array(
                            'type'    => 'Segment',
                            'options' => array(
                                    'route'    => '/auth[/:params]',
                                    'constraints' => array(
                                            'params'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                            'controller'        => 'Index',
                                            'action'        => 'auth',
                                    ),
                            ),
                    ),
                    'callback' => array(
                            'type'    => 'Segment',
                            'options' => array(
                                    'route'    => '/callback[/:code]',
                                    'constraints' => array(
                                            'code'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                            'controller'        => 'Index',
                                            'action'        => 'callback',
                                    ),
                            ),
                    ),
                    'playlist' => array(
                            'type'    => 'Segment',
                            'options' => array(
                                    'route'    => '/playlist[/:id]',
                                    'constraints' => array(
                                            'id'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                            'controller'        => 'Index',
                                            'action'        => 'playlist',
                                    ),
                            ),
                    ),
                    'save' => array(
                            'type'    => 'Segment',
                            'options' => array(
                                    'route'    => '/save',
                                    'constraints' => array(
                                    ),
                                    'defaults' => array(
                                            'controller'        => 'Index',
                                            'action'        => 'save',
                                    ),
                            ),
                    ),
                    'ajax' => array(
                            'type'    => 'Segment',
                            'options' => array(
                                    'route'    => '/ajax[/:action[/:id[/:playlist]]]',
                                    'constraints' => array(
                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                            'controller'        => 'Ajax',
                                            'action'        => 'index',
                                            'id'        => null,
                                    ),
                            ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
        ),
        'factories' => array(
        		'Spotify\Controller\Index' => 'Spotify\Factory\IndexControllerFactory',
        		'Spotify\Controller\Ajax' => 'Spotify\Factory\AjaxControllerFactory',
        )
    ),
    'view_helpers' => array (
    		'invokables' => array (
    		    'spotifybutton' => 'Spotify\View\Helper\SpotifyButton',
    		    'playlistsfromtext' => 'Spotify\View\Helper\SpotifyWidget',
    		    'userplaylists' => 'Spotify\View\Helper\UserPlaylists',
    		    'tracks' => 'Spotify\View\Helper\Tracks'
    		)
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
