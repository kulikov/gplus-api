<?php

error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');
ini_set('display_errors', 'On');
ini_set('include_path', __DIR__ . '/../../phplib' . PATH_SEPARATOR . ini_get('include_path'));


require_once __DIR__ . '/../../phplib/Zend/Json/Decoder.php';
Zend_Json::$useBuiltinEncoderDecoder = true;

require_once __DIR__ . '/../../phplib/Zend/Cache.php';
$cacher = Zend_Cache::factory('Core', 'File', array('lifetime' => 7200), array('cache_dir' => __DIR__ . '/cache/'));

use Gplus\Profile;
require_once 'Gplus/Profile.php';

$profile = new \Gplus\Profile('104578309919492528255');
$profile->setCacher($cacher);

$lastComments = $profile->getLastComments();

header('Content-Type: application/xml; charset=UTF-8');

?>

<rss version="2.0">
<channel>
	<title>Последние комментарии</title>
	<link><?php print $profile->getUrl()?></link>
	<?php foreach ($lastComments as $comment): ?>
	<item>
		<title><![CDATA[<?php print htmlspecialchars($comment->getAuthorName()); ?>]]></title>
		<guid isPermaLink="true"><?php print htmlspecialchars($comment->getUrl()); ?></guid>
		<link><?php print htmlspecialchars($comment->getUrl()); ?></link>
		<description><![CDATA[<?php print $comment->getText(); ?>]]></description>
		<pubDate><?php print date('D, d M Y H:i:s O', $comment->getDate()); ?></pubDate>
		<author><?php print htmlspecialchars($comment->getAuthorName()); ?></author>
	</item>
	<?php endforeach; ?>
</channel>
</rss>