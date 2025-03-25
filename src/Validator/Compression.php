<?php

namespace Alto\Validator;

use Laminas\Validator\AbstractValidator;

class Compression extends AbstractValidator
{
    public const ERR_NO_ZLIB = 'no_zlib';

    protected array $messageTemplates = [
        self::ERR_NO_ZLIB => 'PHP extension zlib is required for compression but is not loaded', // @translate
    ];

    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if ($value && !extension_loaded('zlib')) {
            $this->error(self::ERR_NO_ZLIB);
            return false;
        }

        return true;
    }
}
