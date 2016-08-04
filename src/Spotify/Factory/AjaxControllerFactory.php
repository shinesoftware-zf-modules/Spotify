<?php
namespace Spotify\Factory; 

use Spotify\Controller\AjaxController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AjaxControllerFactory implements FactoryInterface
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
        
        
        return new AjaxController($spotifyService, $spotifyProfileService, $form, $formfilter, $baseSettings);
    }
}