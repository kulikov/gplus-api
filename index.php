<?php


if (preg_match('/^\/demo\d*\.html$/', $_SERVER['REQUEST_URI'])) {
    require_once trim($_SERVER['REQUEST_URI'], '/');
    die;
}

if (empty($_GET['profile'])) {
    header('Location: https://github.com/kulikov/gplus-api');
    die;
}


/**
 * Configure and bootstraping
 */


date_default_timezone_set('Europe/Moscow');
header('Content-type: application/x-javascript; charset=utf-8');

define('DIR_ZEND', realpath(__DIR__ . '/../phplib/zf2/library/Zend'));

require_once DIR_ZEND . '/Loader/StandardAutoloader.php';
$loader = new \Zend\Loader\StandardAutoloader();
$loader->registerNamespace('Zend', DIR_ZEND);
$loader->registerNamespace('Gplus', __DIR__ . '/Gplus');
$loader->register();



/**
 * Application
 */

$api = \Gplus\Api::factory($_GET['profile'], array(
    // 'cacher' => \Zend\Cache\Cache::factory('Core', 'File', array('lifetime' => 7200, 'automatic_serialization' => true), array('cache_dir' => __DIR__ . '/cache/')),
));

try {
    $comments = $api->getPingbackComments(isset($_GET['url']) ? $_GET['url'] : $_SERVER['HTTP_REFERER']);
} catch (Exception $e) {
    $comments = array();
    $error = $e->getMessage();
}
if ($comments) {
    $firstComment = reset($comments);
}

$html = '
<style type="text/css">
    #gplus-pingback-wr * { margin: 0; padding: 0; border: none; }
    #gplus-pingback-wr .gplus-pingback-header { font-size: 16px; font-weight: bold; margin: 20px 0 10px; border-top: #bbb 3px solid; padding: 10px 0 10px 22px; background: url("https://ssl.gstatic.com/s2/oz/images/favicon.ico") no-repeat left center; }
    #gplus-pingback-wr .gplus-pingback-item { border-top: #ccc 1px dotted; position: relative; padding: 8px 8px 8px 50px; }
    #gplus-pingback-wr .gplus-pingback-item:hover { background-color: lightBlue; }
    #gplus-pingback-wr .gplus-pingback-item-text { font-size: 13px; line-height: 1.4; padding-bottom: 2px; }
    #gplus-pingback-wr .gplus-pingback-item-date { font-size: 13px; line-height: 1.4; color: #999; }
    #gplus-pingback-wr .gplus-pingback-item-avatar { position: absolute; top: 8px; left: 8px; text-decoration: none; display: block; }
</style>
<div id="gplus-pingback-wr">
<div class="gplus-pingback-header">'. (!empty($firstComment) ? ('<a href="'. htmlspecialchars($firstComment->getUrl()) .'" target="_blank">') : '') .'Комментарии из Google Plus+'. (!empty($firstComment) ? '</a>' : '') .'</div>
';

if ($comments) {
    foreach ($comments as $comment) {
        $html .= '<div class="gplus-pingback-item">
            <a href="' . htmlspecialchars($comment->getAuthorProfileUrl()) .'" class="gplus-pingback-item-avatar">
                <img src="'. htmlspecialchars($comment->getAuthorPhoto()) .'?sz=32" />
            </a>
            <div class="gplus-pingback-item-text">
                <a href="' . htmlspecialchars($comment->getAuthorProfileUrl()) .'" class="gplus-pingback-item-author" target="_blank">'. htmlspecialchars($comment->getAuthorName()) .'</a>&nbsp;-
                '. $comment->getText() .'
            </div>
            <div class="gplus-pingback-item-date">'. $comment->getFormatedDate() .'</div>
        </div>';
    }
} else {
    $html .= '<div style="margin: -10px 22px 20px;">Комментариев пока нет</div>';
    if (!empty($error)) {
        $html .= '<div style="font-size: 11px; color: #999; margin: -15px 22px 20px;">'. htmlspecialchars($error) .'</div>';
    }
}

$html = str_replace(array("'", "\n", "\r"), array("\\'", '\\n', '\\r'), $html) . '</div>';

echo "(function() {
    document.getElementById('gplus-pingback').innerHTML = '". $html ."';
})();";

