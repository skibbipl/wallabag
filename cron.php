<?php

include_once 'inc/poche/global.inc.php';
include_once 'inc/poche/config.inc.php';

if (php_sapi_name() === 'cli') {
    $options_cli = getopt('', array(
        'limit::',
        'user-id::',
        'token::',
    ));
}
else {
    $options_cli = $_GET;
}

$limit = ! empty($options_cli['limit']) && ctype_digit($options_cli['limit']) ? (int) $options_cli['limit'] : 10;
$user_id = ! empty($options_cli['user-id']) && ctype_digit($options_cli['user-id']) ? (int) $options_cli['user-id'] : null;
$token = ! empty($options_cli['token']) && ctype_alnum($options_cli['token']) ? $options_cli['token'] : null;

if (is_null($user_id)) {
    die('You must give a user id');
}

if (is_null($token)) {
    die('You must give a token');
}

$store = new Database();
$config = $store->getConfigUser($user_id);

if ($token != $config['token']) {
    die(_('Uh, there is a problem with the cron.'));
}

$items = $store->retrieveUnfetchedEntries($user_id, $limit);

include_once 'inc/3rdparty/DummySingleItemFeed.php';
include_once 'inc/3rdparty/fetch_content.php';

foreach ($items as $item) {
    $content = fetchContent($item['url']);
    $store->updateContentAndTitle($item['id'], $content, $user_id);
}
