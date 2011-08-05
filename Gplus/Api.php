<?php

namespace Gplus;

use Zend\Pdf\Action\Thread;

class Api
{
    const
        GPLUS_URL         = 'https://plus.google.com/',
        POSTS_JSON_URL    = 'https://plus.google.com/_/stream/getactivities/%s/?sp=[1,2,"%s",null,null,null,null,"social.google.com",[]]',
        COMMENTS_JSON_URL = 'https://plus.google.com/_/stream/getactivity/?updateId=%s';

    private
        $_cacher  = null,
        $_profile = null;


    public static function factory($profileId, array $options = array())
    {
        if (!$profileId) {
            throw new \Exception('Set your G+ profile id');
        }

        $api = new self();

        $api->setProfile(new Profile($profileId));

        if (isset($options['cacher'])) {
            $api->setCacher($options['cacher']);
        }

        return $api;
    }

    public function setProfile(Profile $profile)
    {
        $this->_profile = $profile;
        return $this;
    }

    
    public function getProfile()
    {
        return $this->_profile;
    }


	public function setCacher(\Zend\Cache\Frontend $cacher)
    {
        $this->_cacher = $cacher;
        return $this;
    }


    public function getPingbackComments($url)
    {
        if (!$url) {
            throw new \Exception('Set url for pingback!');
        }

        /* Сначала смотрим есть ли эта ссылка в кеше */
        if ($post = $this->_getPostByUrlFromCache($url)) {
            return $this->getPostComments($post);
        }

        $posts = $this->getLastPosts();

        rsort($posts); // ищем первое упоминание

        foreach ($posts as $post) {
            if ($post->containsString($url)) {

                /**
                 * Сохраняем пост, в котором была найденна ссылка
                 * В следующий раз, когда $this->getLastPosts() не вернет нам нужный пост мы будем доставать комменты из заранее известного поста
                 */
                $this->_savePostToUrlLink($post, $url);

                return $this->getPostComments($post);
            }
        }

        return array();
    }


    public function getPostComments(Post $post)
    {
        $_commentsUrl = sprintf(self::COMMENTS_JSON_URL, $post->getId());

        $content = $this->_cache(3600, $_commentsUrl, function() use ($_commentsUrl) {

            $client = new \Zend\Http\Client($_commentsUrl);
            $content = $client->request()->getBody();
            return \Zend\Json\Decoder::decode(substr($content, 5), \Zend\Json\Json::TYPE_ARRAY);
        });

        if (empty($content[1][7])) {
            throw new \Exception('Сomments not found');
        }

        $output = array();
        foreach ($content[1][7] as $comment) {
            $output[] = new Comment(array(
                'authorName'  => $comment[1],
                'authorPhoto' => $comment[16],
                'authorId'    => $comment[6],
                'text'        => $comment[2],
                'url'         => $post->getUrl(),
                'date'        => round($comment[3] / 1000),
            ));
        }

        uasort($output, function($c1, $c2) { return $c1->getDate() > $c2->getDate(); });

        return $output;
    }

    public function getLastPosts()
    {
        $_profileId = $this->_profile->getId();
        $_postUrl   = sprintf(self::POSTS_JSON_URL, $_profileId, $_profileId);

        $content = $this->_cache(3600, $_postUrl, function() use ($_postUrl) {

            $client = new \Zend\Http\Client($_postUrl);
            $content = $client->request()->getBody();

            if (!$content) {
                throw new \Exception('Error fetch posts');
            }

            return \Zend\Json\Decoder::decode(substr($content, 5), \Zend\Json\Json::TYPE_ARRAY);
        });

        $output = array();
        foreach ($content[1][0] as $_post) {
            $output[] = new Post(array(
                'id'         => $_post[8],
                'authorName' => $_post[3],
                'date'       => round($_post[5] / 1000),
                'text'       => $_post[4],
                'authorName' => $_post[8],
                'url'        => self::GPLUS_URL . $_post[21],
                'allContent' => $_post,
            ));
        }

        return $output;
    }




    /* PRIVATE */

    private function __construct()
    {
    }


    private function _cache($lifetime, $key, $callback)
    {
        $key = md5($key);

        if ($this->_cacher) {
            if ($result = $this->_cacher->load($key)) {
                return $result;
            }
        }

        $result = $callback();

        if ($this->_cacher) {
            $this->_cacher->save($result, $key);
        }

        return $result;
    }


    private function _savePostToUrlLink(Post $post, $url)
    {
        $this->_getPostToUrlLinkStorage()->save(array(
            'id'  => $post->getId(),
            'url' => $post->getUrl(),
        ), md5($url . $this->_profile->getId()));
    }

    private function _getPostByUrlFromCache($url)
    {
        if ($postData = $this->_getPostToUrlLinkStorage()->load(md5($url . $this->_profile->getId()))) {
            return new Post($postData);
        }
        return false;
    }

    /**
     * @return \Zend\Cache\Frontend
     */
    private function _getPostToUrlLinkStorage()
    {
        return \Zend\Cache\Cache::factory('Core', 'File', array('lifetime' => null, 'automatic_serialization' => true), array('cache_dir' => realpath(__DIR__ . '/../cache')));
    }
}
