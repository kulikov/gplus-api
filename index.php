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

ini_set('display_errors', 'off');
date_default_timezone_set('Europe/Moscow');
header('Content-type: application/x-javascript; charset=utf-8');

define('DIR_ZEND', realpath(__DIR__ . '/../phplib/zf2/library/Zend'));

require_once DIR_ZEND . '/Loader/StandardAutoloader.php';
$loader = new \Zend\Loader\StandardAutoloader();
$loader->registerNamespace('Zend', DIR_ZEND);
$loader->registerNamespace('Gplus', __DIR__ . '/Gplus');
$loader->register();




/**
 * Controller
 */

$api = \Gplus\Api::factory($_GET['profile'], array(
    // 'cacher' => \Zend\Cache\Cache::factory('Core', 'File', array('lifetime' => 7200, 'automatic_serialization' => true), array('cache_dir' => __DIR__ . '/cache/')),
));

try {
    $orderBy  = isset($_COOKIE['gplusOrder']) ? $_COOKIE['gplusOrder'] : null;
    $comments = $api->getPingbackComments(isset($_GET['url']) ? $_GET['url'] : $_SERVER['HTTP_REFERER'], $orderBy);
} catch (Exception $e) {
    $comments = array();
    $error    = $e->getMessage();
}

if ($comments) {
    $firstComment = reset($comments);
}



/**
 * View
 */

$html = '
<style type="text/css">
    #gplus-pbwr * { margin: 0; padding: 0; border: none; }
    #gplus-pbwr .gplus-pbh { margin: 20px 0 10px; border-top: #bbb 3px solid; padding: 10px 0 10px 22px; background: url("https://ssl.gstatic.com/s2/oz/images/favicon.ico") no-repeat left center; }
    #gplus-pbwr .gplus-pbh-title { font-size: 16px; font-weight: bold; }
    #gplus-pbwr .gplus-pbh-order { float: right; font-size: 12px; padding: 4px 0; }
    #gplus-pbwr .gplus-pbh-order a { text-decoration: none; margin-left: 3px; padding: 3px 4px; }
    #gplus-pbwr .gplus-pbh-order a u { text-decoration: none; border-bottom: 1px dotted; }
    #gplus-pbwr .gplus-pbh-order .gplus-active { background: #d0e7fd; color: black; }
    #gplus-pbwr .gplus-pbh-order .gplus-active u { border: none; }
    #gplus-pbwr .gplus-pbi { border-top: #ccc 1px dotted; position: relative; padding: 8px 8px 8px 50px; }
    #gplus-pbwr .gplus-pbi-avatar { position: absolute; top: 8px; left: 8px; text-decoration: none; display: block; }
    #gplus-pbwr .gplus-pbi-text { font-size: 13px; line-height: 1.4; padding-bottom: 2px; }
    #gplus-pbwr .gplus-pbi-footer { font-size: 13px; line-height: 1.4; }
    #gplus-pbwr .gplus-pbi-date { margin-right: 10px; color: #999; }
    #gplus-pbwr .gplus-pbi-plusone { color: #3366CC; font-style: italic; font-weight: bold; }
</style>
<div id="gplus-pbwr">
<div class="gplus-pbh">
    <div class="gplus-pbh-order">
        <a href="#" onclick="GplusApi.sortBy(this, \'date\', 1); return false;" '. ($orderBy != 'gplusDesc' && $orderBy != 'dateDesc' ? 'class="gplus-active"' : '') .'><u>новые снизу</u> &darr;</a>
        <a href="#" onclick="GplusApi.sortBy(this, \'date\', 0); return false;" '. ($orderBy == 'dateDesc' ? 'class="gplus-active"' : '') .'><u>новые сверху</u> &uarr;</a>
        <a href="#" onclick="GplusApi.sortBy(this, \'gplus\'); return false;" '. ($orderBy == 'gplusDesc' ? 'class="gplus-active"' : '') .'><u>по рейтингу</u> +1</a>
    </div>
    <a class="gplus-pbh-title" href="'. htmlspecialchars(!empty($firstComment) ? $firstComment->getUrl() : $api->getProfile()->getUrl()) .'" target="_blank">Комментарии из Google Plus+</a>
</div>
<div id="gplus-pbwr-items">
';

if ($comments) {
    foreach ($comments as $comment) {
        $pgVal = $comment->getPlusOneValue();
        $html .= '<div class="gplus-pbi" date="'. $comment->getDate() .'" gplus="'. $pgVal .'">
            <a href="' . htmlspecialchars($comment->getAuthorProfileUrl()) .'" class="gplus-pbi-avatar">
                <img src="'. htmlspecialchars($comment->getAuthorPhoto()) .'?sz=32" />
            </a>
            <div class="gplus-pbi-text">
                <a href="' . htmlspecialchars($comment->getAuthorProfileUrl()) .'" class="gplus-pbi-author" target="_blank">'. htmlspecialchars($comment->getAuthorName()) .'</a>&nbsp;-
                '. $comment->getText() .'
            </div>
            <div>
                <span class="gplus-pbi-date">'. $comment->getFormatedDate() .'</span>
                '. (($pgVal) ? '<span class="gplus-pbi-plusone">+'. $pgVal .'</span>' : '') .'
            </div>
        </div>';
    }
} else {
    $html .= '<div style="margin: -15px 22px 30px;">Пока ничего нет</div>';
    if (!empty($error)) {
        $html .= '<div style="font-size: 11px; color: #999; margin: -25px 22px 30px;">'. htmlspecialchars($error) .'</div>';
    }
}

$html = str_replace(array("'", "\n", "\r"), array("\\'", '\\n', '\\r'), $html) . '</div></div>';

echo preg_replace('/\s+/u', ' ', "(function() {
    var _g = document.getElementById('gplus-pingback');
    if (!_g) {
        _g = document.createElement('div'); _g.id = 'gplus-pingback';
        var _c = document.getElementById('comments'); _c.parentNode.insertBefore(_g, _c.nextSibling);
    }
    _g.innerHTML = '". $html ."';

    GplusApi = {
        sortBy: function(button, field, dir) {
            var list = document.getElementById('gplus-pbwr-items'), items = list.childNodes, itemsArr = [];
            for (var i in items) items[i].nodeType == 1 && itemsArr.push(items[i]);
            itemsArr.sort(function(a, b) {
                a = parseInt(a.getAttribute(field), 10), b = parseInt(b.getAttribute(field), 10);
                return a == b ? 0 : (a > b ? (dir ? 1 : -1) : (dir ? -1 : 1));
            });
            for (i in itemsArr) {
              list.appendChild(itemsArr[i]);
            }
            var bns = button.parentNode.getElementsByTagName('a');
            for (i in bns) bns[i].className = '';
            button.className = 'gplus-active';
            document.cookie = 'gplusOrder=' + field + (dir ? 'Asc' : 'Desc');
        }
    };
})();");
