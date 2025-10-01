<?php

return [

    'required' => 'Le champ :attribute est obligatoire.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'max' => [
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],
    'confirmed' => 'La confirmation de :attribute ne correspond pas.',
    'unique' => 'Ce :attribute est déjà utilisé.',
    'exists' => 'Le :attribute sélectionné est invalide.',
    'password' => 'Le mot de passe est incorrect.',

    'attributes' => [
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
    ],

];
