<?php
namespace Spotify\Factory; 

use Spotify\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $baseSettings = $realServiceLocator->get('SettingsService');
        $spotifyService = $realServiceLocator->get('SpotifyService');
        $spotifyProfileService = $realServiceLocator->get('SpotifyProfileService');
        
        $form = $realServiceLocator->get('FormElementManager')->get('Spotify\Form\SpotifyForm');
        $formfilter = $realServiceLocator->get('SpotifyFilter');
        
        
        return new IndexController($spotifyService, $spotifyProfileService, $form, $formfilter, $baseSettings);
    }
}