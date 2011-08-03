<?php

/** * Configure and bootstraping */

if ($_SERVER['REQUEST_URI'] == '/demo.html') {
	require_once 'demo.html';
	die;
}

date_default_timezone_set('Europe/Moscow');
header('Content-type: application/x-javascript; charset=utf-8');

define('DIR_ZEND', realpath(__DIR__ . '/../phplib/zf2/library/Zend'));

require_once DIR_ZEND . '/Loader/StandardAutoloader.php';
$loader = new \Zend\Loader\StandardAutoloader();
$loader->registerNamespace('Zend', DIR_ZEND);
$loader->registerNamespace('Gplus', __DIR__ . '/Gplus');
$loader->register();



/** * Application */

$api = \Gplus\Api::factory($_GET['profile'], array(
	'cacher' => \Zend\Cache\Cache::factory('Core', 'File', array('lifetime' => 7200, 'automatic_serialization' => true), array('cache_dir' => __DIR__ . '/cache/')),
));

$comments = $api->getPingbackComments(isset($_GET['url']) ? $_GET['url'] : $_SERVER['REFERER']);

$html = '
<style type="text/css">
	#gplus-pingback .gplus-pingback-header { margin: 10px 0; border-top: #bbb 3px solid; padding: 10px 0 10px 22px; background: url("https://ssl.gstatic.com/s2/oz/images/favicon.ico") no-repeat left center; }
	#gplus-pingback .gplus-pingback-item { border-top: #ccc 1px dotted; position: relative; padding: 8px 8px 8px 50px; }
	#gplus-pingback .gplus-pingback-item-text { font-size: 13px; line-height: 1.4; padding-bottom: 2px; }
	#gplus-pingback .gplus-pingback-item-date { font-size: 13px; line-height: 1.4; color: #999; }
	#gplus-pingback .gplus-pingback-item-avatar { position: absolute; top: 8px; left: 8px; }
</style>
<div id="gplus-pingback">
<h3 class="gplus-pingback-header">Комментарии из Google Plus+</h3>
';

foreach ($comments as $comment) {
	$html .= '<div class="gplus-pingback-item">
        <img src="'. htmlspecialchars($comment->getAuthorPhoto()) .'?sz=32" class="gplus-pingback-item-avatar" />
        <div class="gplus-pingback-item-text">
        	<a href="#" class="gplus-pingback-item-author">'. htmlspecialchars($comment->getAuthorName()) .'</a>&nbsp;-&nbsp;
        	'. $comment->getText() .'
        </div>
        <div class="gplus-pingback-item-date">'. date('d.m.Y H:i', $comment->getDate()) .'</div>
    </div>';
}

$html = str_replace(array("'", "\n", "\r"), array("\\'", '\\n', '\\r'), $html) . '</div>';

echo "(function() {
    document.getElementById('gplus-pingback').innerHTML = '". $html ."';
})();";


