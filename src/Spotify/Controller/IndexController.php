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

class IndexController extends AbstractActionController
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
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->translator = $e->getApplication()->getServiceManager()->get('translator');

        return parent::onDispatch($e);
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


    /**
     * Get the list of the active and visible spotify
     *
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $form = $this->form;
        $vm = new ViewModel(array('form' => $form));
        $vm->setTemplate('spotify/index/auth');
        return $vm;
    }

    /**
     * Get the list of the active and visible spotify
     *
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.AbstractActionController::indexAction()
     */
    public function saveAction()
    {
        $inputFilter = $this->filter;
        $post = $this->request->getPost();
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        if (!$this->request->isPost()) {
            return $this->redirect()->toRoute(NULL, array(
                'action' => 'index'
            ));
        }

        $form = $this->form;
        $form->setData($post);
        $form->setInputFilter($inputFilter);

        if (!$form->isValid()) {

            // Get the record by its id
            $viewModel = new ViewModel(array(
                'error' => true,
                'form' => $form,
            ));

            $viewModel->setTemplate('spotify/index/pages');
            return $viewModel;
        }

        // Get the posted vars
        $data = $form->getData();

        if (!empty($data['playlists'])) {
            // Checking if the preference is already set
            $playlist = $this->spotifyProfile->findByCodeAndUserId('playlist', $userId);
            if ($playlist) {
                $this->spotifyProfile->deleteAllbyParameter("playlist", $userId);
            }

            $gSetting = new \Spotify\Entity\SpotifyProfiles();
            $gSetting->setParameter('playlist');
            $gSetting->setValue($data['playlists']);
            $gSetting->setUserId($userId);
            $gSetting->setCreatedat(date('Y-m-d H:i:s'));
            $pSettingService = $this->spotifyProfile->save($gSetting);
        }


        $this->flashMessenger()->setNamespace('success')->addMessage($this->translator->translate('The information have been saved.'));

        return $this->redirect()->toUrl('/spotify/playlist');
    }


    /**
     * Get the list of the active and visible spotify
     *
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.AbstractActionController::indexAction()
     */
    public function playlistAction()
    {
        $form = $this->form;
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $playlist = $this->spotifyProfile->findByCodeAndUserId('playlist', $userId);
        if ($playlist) {
            $form->setData(array('playlists' => $playlist->getValue()));
        }

        $vm = new ViewModel(array('form' => $form));
        $vm->setTemplate('spotify/index/playlists');
        return $vm;
    }


    /**
     * get the spotify user permissions
     */
    public function authAction()
    {

        $config = $this->getServiceLocator()->get('config');
        $session = new \SpotifyWebAPI\Session($config['SpotifyClient']['client_id'], $config['SpotifyClient']['client_secret'], $config['SpotifyClient']['redirect_uri']);
        $scopes = array(
            'playlist-read-private',
            'user-read-private'
        );

        $authorizeUrl = $session->getAuthorizeUrl(array(
            'scope' => $scopes
        ));

        header('Location: ' . $authorizeUrl);
        die();

    }

    /**
     * Callback called by the Spotify service
     * @throws \Exception
     */
    public function callbackAction()
    {

        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $config = $this->getServiceLocator()->get('config');
        if (!empty($config['SpotifyClient'])) {

            $session = new \SpotifyWebAPI\Session($config['SpotifyClient']['client_id'], $config['SpotifyClient']['client_secret'], $config['SpotifyClient']['redirect_uri']);

            $api = new \SpotifyWebAPI\SpotifyWebAPI();

            // Request a access token using the code from Spotify
            $session->requestToken($_GET['code']);
            $accessToken = $session->getAccessToken();

            // Set the access token on the API wrapper
            $api->setAccessToken($accessToken);
            $user = $api->me();

            $refreshToken = $session->getRefreshToken();

            $record = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('display_name', $userId);
            if (!$record) {
                $record = new \Spotify\Entity\SpotifyProfiles();
            }
            // Save the profile
            $record->setCreatedat(date('Y-m-d H:i:s'));
            $record->setUserId($userId);
            $record->setParameter('display_name');

            if (!empty($user->display_name)) {
                $record->setValue($user->display_name);
            } else {
                $record->setValue('No name');
            }

            $this->spotifyProfile->save($record);

            $record = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('user_id', $userId);
            if (!$record) {
                $record = new \Spotify\Entity\SpotifyProfiles();
            }

            // Save the profile
            $record->setCreatedat(date('Y-m-d H:i:s'));
            $record->setUserId($userId);
            $record->setParameter('user_id');
            $record->setValue($user->id);
            $this->spotifyProfile->save($record);

            $record = $this->getServiceLocator()->get('SpotifyProfileService')->findByCodeAndUserId('access_token', $userId);
            if (!$record) {
                $record = new \Spotify\Entity\SpotifyProfiles();
            }

            // Save the profile
            $record->setCreatedat(date('Y-m-d H:i:s'));
            $record->setUserId($userId);
            $record->setParameter('access_token');
            $record->setValue($refreshToken);
            $this->spotifyProfile->save($record);

            $this->flashMessenger()->setNamespace('success')->addMessage($this->translator->translate('Spotify has been set!'));

            return $this->redirect()->toUrl('/spotify/playlist');
        } else {
            throw new \Exception('You have to config your spotify.local.php file!');
        }
    }
}
