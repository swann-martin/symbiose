<?

use core\User;

list($params, $providers) = announce([
    'description' => "",
    'params' => [],
    'response' => [
        'content-type' => 'application/json',
        'charset' => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers' => ['context', 'orm', 'auth']
]);

list($orm, $context, $auth) = [$providers['orm'], $providers['context'], $providers['auth'],];

// id of the group qursus defined in init/data
$qursus_group = 3;
$user_id = $auth->userId();
$user_groups = User::Id($user_id)->read('groups_ids')->first(true);
$user_is_in_qursus_group = in_array($qursus_group, array_column($user_groups, 'groups_ids'));

$context->httpResponse()
    ->status(200)
    ->body($user_is_in_qursus_group)
    ->send();
