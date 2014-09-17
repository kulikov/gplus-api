<?php

namespace Gplus;

use Zend;

class Api
{
    const
        GPLUS_URL         = 'https://plus.google.com/',
        POSTS_JSON_URL    = 'https://www.googleapis.com/plus/v1/people/%s/activities/public',
        COMMENTS_JSON_URL = 'https://www.googleapis.com/plus/v1/activities/%s/comments';

    private
        $_apiKey        = null,
        $_profile       = null,
        $_searchStorage = null;


    public static function factory($profileId, array $options = array())
    {
        if (!$profileId) {
            throw new \Exception('Set your G+ profile id');
        }

        $api = new self();

        $api->setProfile(new Profile($profileId));

        if (isset($options['apiKey'])) {
            $api->_apiKey = $options['apiKey'];
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

        $client = new Zend\Http\Client($_commentsUrl);
        $content = $client->setParameterGet(array(
            'key' => $this->_apiKey,
            'maxResults' => 10,
            'fields' => 'items(actor(displayName,id,image),object/content,published,plusoners/totalItems)',
        ))->send()->getBody();

        $content = Zend\Json\Decoder::decode($content, Zend\Json\Json::TYPE_ARRAY);

        if (!empty($content['error'])) {
            throw new \Exception('Post not found. ' . $content['error']['message']);
        }

        if (empty($content['items'])) {
        	return array();
        }

        $output = array();
        foreach ($content['items'] as $comment) {
            $output[] = new Comment(array(
                'authorName'   => $comment['actor']['displayName'],
                'authorPhoto'  => $comment['actor']['image']['url'],
                'authorId'     => $comment['actor']['id'],
                'text'         => $comment['object']['content'],
                'url'          => $post->getUrl(),
                'date'         => strtotime($comment['published']),
                'plusOneValue' => $comment['plusoners']['totalItems'],
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
        $_postUrl   = sprintf(self::POSTS_JSON_URL, $_profileId);
        
        $client = new Zend\Http\Client($_postUrl);
        $content = $client->setParameterGet(array(
            'key' => $this->_apiKey, 
            'maxResults' => '50',
            'fields' => 'items(actor/displayName,id,object(attachments(content,displayName,embed/url,url),content),published,url)',
        ))->send()->getBody();

        if (!$content) {
            throw new \Exception('Error fetch posts');
        }

        $content = Zend\Json\Decoder::decode($content, Zend\Json\Json::TYPE_ARRAY);

        if (empty($content['items'])) {
            throw new \Exception('Posts not found!');
        }

        $output = array();
        foreach ($content['items'] as $_post) {
            $output[] = new Post(array(
                'id'         => $_post['id'],
                'authorName' => $_post['actor']['displayName'],
                'date'       => strtotime($_post['published']),
                'text'       => $_post['object']['content'],
                'url'        => $_post['url'],
                'allContent' => array( // тут будет искаться ссылка для pingback'а
                    isset($_post['object']['attachments']) ? $_post['object']['attachments'] : ''
                ),
            ));
        }

        return $output;
    }


    /**
     * Хелпер для правильного вывода кол-ва комментов
     *
     * <code>
     *     Api:inciting(12, 'комментарий', 'комментария', 'комментариев');
     * </code>
     */
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
        $this->_getPostSearchStorage()->setItem(array(
            'id'            => $post->getId(),
            'url'           => $post->getUrl(),
        	'_searchString' => $string,
        ), md5($string . $this->_profile->getId()));
    }

    private function _getPostByStringFromCache($string)
    {
        if ($postData = $this->_getPostSearchStorage()->getItem(md5($string . $this->_profile->getId()))) {
            return new Post($postData);
        }
        return false;
    }

    /**
     * @return Zend\Cache\Frontend
     */
    private function _getPostSearchStorage()
    {
        if ($this->_searchStorage === null) {
            $this->_searchStorage = Zend\Cache\StorageFactory::factory(array(
                'adapter' => 'Filesystem',
                'options' => array(
                    'cacheDir' => realpath(__DIR__ . '/../cache'),
                ),
                'plugins' => array('Serializer'),
            ));
        }
        return $this->_searchStorage;
    }
}
