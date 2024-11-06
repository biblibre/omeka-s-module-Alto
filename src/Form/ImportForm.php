<?php
namespace Alto\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Checkbox;

class ImportForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'files',
            'type' => File::class,
            'options' => [
                'label' => 'ALTO files', // @translate
            ],
            'attributes' => [
                'accept' => '.xml,application/alto+xml,application/xml',
                'required' => true,
                'multiple' => true,
            ],
        ]);

        $this->add([
            'name' => 'replace',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Replace existing ALTO documents', // @translate
                'info' => 'If checked, matching media which already have an ALTO document attached will have their ALTO document replaced', // @translate
            ],
        ]);
    }
}
