<?php 

namespace Spotify\View\Helper;
use Zend\View\Helper\AbstractHelper;

class SpotifyWidget extends AbstractHelper {
	
    public function __invoke($userId, $content)
    {
       $re = "/(?<=user\\/)(\\w+).*(?<=playlist\\/)(\\w+)/"; 
       $playlists = array();
       
       preg_match_all($re, $content, $matches, PREG_SET_ORDER);
       
       foreach ($matches as $match){
           $playlists[$match[1]]['user'] = $match[1];
           $playlists[$match[1]]['playlist'] = $match[2];
       }
       
       return $this->view->render('spotify/partial/widget', array('playlists' => $playlists));
    }
}