<?php
return [
  'bank' => [
    'holder' => env('BANK_HOLDER', 'JarvisTech SPRL'),
    'iban'   => env('BANK_IBAN', 'BE00 0000 0000 0000'),
    'bic'    => env('BANK_BIC',  'BBRUBEBB'),
    'note'   => env('BANK_NOTE', 'Merci d’indiquer la référence dans le motif.'),
  ],
  'payment_deadline_days' => env('PAYMENT_DEADLINE_DAYS', 5),
];