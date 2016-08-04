<?php
namespace Spotify\Form;
use Zend\InputFilter\InputFilter;

class SpotifyFilter extends InputFilter
{

    public function __construct ()
    {
    	$this->add(array (
    			'name' => 'playlists',
    			'required' => false
    	));
    	
    }
}