<?php

# User Attension : Please don't change this configuration.
#
# If you change this configuration,the system not working properly.

// Installer Property (Don't Edit This)

return [

    // Min php version here
    'min_php' => '8.1',

    // SQL file location
    'sql_file' => base_path('DB/digibank.sql'),

    // All required extensions name here
    'extensions' => [
        'curl' => 'cURL',
        'openssl' => 'OpenSSL',
        'pcre' => 'PCRE',
        'mbstring' => 'Mbstring',
        'ctype' => 'Ctype',
        'bcmath' => 'BCMath',
        'fileinfo' => 'Fileinfo',
        'xml' => 'XML',
        'tokenizer' => 'Tokenizer',
        'json' => 'JSON',
    ]
];
