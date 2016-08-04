<?php
namespace Spotify\Form;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ClassMethods;

class SpotifyForm extends Form
{

    public function init ()
    {

        $this->setAttribute('method', 'post');
        
        $this->add(array (
        		'type' => 'Spotify\Form\Element\Playlists',
        		'name' => 'playlists',
        		'attributes' => array (
        				'class' => 'form-control'
        		),
        		'options' => array (
        				'label' => _('Spotify preferite playlist'),
        		        'disable_inarray_validator' => true,
        		)
        ));
        
        $this->add(array ( 
                'name' => 'submit', 
                'attributes' => array ( 
                        'type' => 'submit', 
                        'class' => 'btn btn-success', 
                        'value' => _('Save your preference')
                )
        ));
        $this->add(array (
                'name' => 'id',
                'attributes' => array (
                        'type' => 'hidden'
                )
        ));
    }
}