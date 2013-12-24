<?php

include_once 'inc/poche/global.inc.php';
include_once 'inc/poche/config.inc.php';

if (php_sapi_name() === 'cli') {
    $options = getopt('', array(
        'limit::',
        'user-id::',
        'token::',
    ));
}
else {
    $options = $_GET;
}

$limit = ! empty($options['limit']) && ctype_digit($options['limit']) ? (int) $options['limit'] : 10;
$user_id = ! empty($options['user-id']) && ctype_digit($options['user-id']) ? (int) $options['user-id'] : null;
$token = ! empty($options['token']) && ctype_alnum($options['token']) ? $options['token'] : null;

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

foreach ($items as $item) {
    $json = file_get_contents('/inc/3rdparty/makefulltextfeed.php?url='.urlencode($item['url']).'&max=5&links=preserve&exc=&format=json&submit=Create+Feed', FILE_USE_INCLUDE_PATH);
    $content = json_decode($json, true);
    $title = $content['rss']['channel']['item']['title'];
    $body = $content['rss']['channel']['item']['description'];

    var_dump($title);die;

    if ($item_to_update = $store->fetch_content($item['id'], $user_id)) {
        Model\update_item($item_to_update);
    }
}
