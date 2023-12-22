<?php

use qursus\UserAccess;

list($params, $providers) = announce([
    'description'   => "Checks if current user has a license for a given program.",
    'params'        => [
        'id' =>  [
            'description'   => 'Pack identifier (id field).',
            'type'          => 'integer',
            'required'      => true
        ],
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\orm\ObjectManager            $orm
 * @var \equal\auth\AuthenticationManager   $auth
 */
list($context, $orm, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

/*
    Retrieve current user id
*/

// if(!isset($_COOKIE) || !isset($_COOKIE["wp_lms_user"]) || !is_numeric($_COOKIE["wp_lms_user"])) {
//     throw new Exception('unknown_user', QN_ERROR_NOT_ALLOWED);
// }

// $user_id = (int) $_COOKIE["wp_lms_user"];

// if($user_id <= 0) {
//     throw new Exception('unknown_user', QN_ERROR_NOT_ALLOWED);
// }

$user_id = $auth->userId();

/*
    Check if user is granted access
*/

UserAccess::create(['pack_id' => $params['id'], 'user_id' => $user_id])->read(['code']);


$context->httpResponse()
    ->status(204)
    ->send();
