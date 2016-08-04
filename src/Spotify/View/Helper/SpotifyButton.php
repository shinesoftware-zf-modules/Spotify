<?php 

namespace Spotify\View\Helper;
use Zend\View\Helper\AbstractHelper;

class SpotifyButton extends AbstractHelper {
	
    public function __invoke($content)
    {
       $re = "/(https?:\\/\\/.+?(?<=user\\/)(\\w+).*(?<=playlist\\/)(\\w+))/"; 
       $playlist = "";
       
       preg_match_all($re, $content, $matches, PREG_SET_ORDER);
       foreach ($matches as $match){
           $playlist['link'] = $match[1];
           $playlist['user'] = $match[2];
           $playlist['playlist'] = $match[3];
           
           $button = $this->view->render('spotify/partial/buttons', array('playlist' => $playlist));
           $content = str_replace($playlist['link'], trim($button), $content);
       }
       
       return $content;
       
    }
}