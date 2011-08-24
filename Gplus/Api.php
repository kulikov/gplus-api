<?php

namespace Gplus;

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


    public function findPostByString($string)
    {
        if (!$string) {
            throw new \Exception('Set string for matching!');
        }

        /* Сначала смотрим есть ли эта ссылка в кеше */
        if ($post = $this->_getPostByStringFromCache($string)) {
            return $post;
        }

        $posts = $this->getLastPosts();

        rsort($posts); // ищем первое упоминание

        foreach ($posts as $post) {
            if ($post->containsString($string)) {

                /**
                 * Сохраняем пост, в котором была найденна ссылка
                 * В следующий раз, когда $this->getLastPosts() не вернет нам нужный пост мы будем доставать комменты из заранее известного поста
                 */
                $this->_savePostSearchLink($post, $string);

                return $post;
            }
        }

        throw new \Exception('Post not found!');
    }


    public function getPostComments(Post $post, $orderBy = null)
    {
        $_commentsUrl = sprintf(self::COMMENTS_JSON_URL, $post->getId());

        $content = $this->_cache(3600, $_commentsUrl, function() use ($_commentsUrl) {

            $client = new \Zend\Http\Client($_commentsUrl);
            $content = $client->request()->getBody();
            return \Zend\Json\Decoder::decode(substr($content, 5), \Zend\Json\Json::TYPE_ARRAY);
        });

        if (empty($content[1])) {
            throw new \Exception('Post not found');
        }

        if (empty($content[1][7])) {
        	return array();
        }

        $output = array();
        foreach ($content[1][7] as $comment) {
            $output[] = new Comment(array(
                'authorName'   => $comment[1],
                'authorPhoto'  => $comment[16],
                'authorId'     => $comment[6],
                'text'         => $comment[2],
                'url'          => $post->getUrl(),
                'date'         => round($comment[3] / 1000),
                'plusOneValue' => $comment[15][16],
            ));
        }

        uasort($output, function($c1, $c2) use ($orderBy) {
        	switch ($orderBy) {
        		case 'gplusDesc':
        			return $c1->getPlusOneValue() < $c2->getPlusOneValue();
        		case 'dateDesc':
        			return $c1->getDate() < $c2->getDate();
        		default:
        			return $c1->getDate() > $c2->getDate();
        	}

        });

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
                'allContent' => array($_post[3], $_post[4], $_post[66]), // тут будет искаться ссылка для pingback'а
            ));
        }

        return $output;
    }


    /**     * Хелпер для правильного вывода кол-ва комментов
     *
     * <code>
     *     Api:inciting(12, 'комментарий', 'комментария', 'комментариев');
     * </code>     */
	public static function inciting($d, $form1, $form2, $form5 = false)
	{
	    $d      = (string) $d;
	    $n      = abs($d) % 100;
	    $n1     = $n % 10;
	    $form5  = $form5 ?: $form2;
	    $prefix = $d . ' ';
	    if ($n > 10 && $n < 20) return $prefix . $form5;
	    if ($n1 > 1 && $n1 < 5) return $prefix . $form2;
	    if ($n1 == 1) return $prefix . $form1;
	    return $prefix . $form5;
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


    private function _savePostSearchLink(Post $post, $string)
    {
        $this->_getPostSearchStorage()->save(array(
            'id'            => $post->getId(),
            'url'           => $post->getUrl(),
        	'_searchString' => $string,
        ), md5($string . $this->_profile->getId()));
    }

    private function _getPostByStringFromCache($string)
    {
        if ($postData = $this->_getPostSearchStorage()->load(md5($string . $this->_profile->getId()))) {
            return new Post($postData);
        }
        return false;
    }

    /**
     * @return \Zend\Cache\Frontend
     */
    private function _getPostSearchStorage()
    {
        return \Zend\Cache\Cache::factory('Core', 'File', array('lifetime' => null, 'automatic_serialization' => true), array('cache_dir' => realpath(__DIR__ . '/../cache')));
    }
}
