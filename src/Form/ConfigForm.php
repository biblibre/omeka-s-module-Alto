<?php

namespace Alto\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'alto_compression',
            'type' => 'Laminas\Form\Element\Checkbox',
            'options' => [
                'label' => 'Use compression', // @translate
                'info' => 'Compress alto documents to reduce the space taken in database. Requires zlib extension', // @translate
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
        ]);

        $this->add([
            'name' => 'alto_start_compression_job',
            'type' => 'Laminas\Form\Element\Checkbox',
            'options' => [
                'label' => 'Start compression background job', // @translate
                'info' => 'Start background job to compress (or decompress, depending on the setting above) alto documents', // @translate
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
        ]);

        $inputFilter = $this->getInputFilter();

        $inputFilter->add([
            'name' => 'alto_compression',
            'validators' => [
                [
                    'name' => 'Alto\Validator\Compression',
                    'options' => [],
                ],
            ],
        ]);
    }
}
