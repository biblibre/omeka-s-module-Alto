<?php
namespace Alto\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;

class MediaForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'file',
            'type' => File::class,
            'options' => [
                'label' => 'ALTO file', // @translate
            ],
            'attributes' => [
                'accept' => '.xml,application/alto+xml,application/xml',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'attach',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Attach', // @translate
            ],
        ]);
    }
}
