<?php

namespace Gplus;

require_once 'Post.php';
require_once 'Comment.php';

class Profile
{
    const
        COMMENTS_URL = 'https://plus.google.com/_/stream/getactivity/?updateId=%s',
        POSTS_URL    = 'https://plus.google.com/%s/posts';
    
    private
        $_cacher    = null,
        $_profileId = null;

    public function __construct($profileId)
    {
        $this->_profileId = $profileId;
    }
    
    public function getId()
    {
        return $this->_profileId;
    }
    
    public function getLastComments()
    {
        $output = array();
        foreach ($this->getLastPosts() as $post) {
            foreach ($post->getComments() as $comment) {
                $output[] = $comment;
            }
        }
        
        usort($output, function($c1, $c2) { return $c1->getDate() < $c2->getDate(); });
        
        return $output;
    }

    public function getLastPosts()
    {
        $content = $this->_cache(3600, $this->_profileId, function(Profile $profile) {
            return file_get_contents($profile->getUrl());
        });
        
        if (!preg_match_all('/"update-([^\"]+)"/', $content, $matches)) {
            throw new \Exception('No match posts');
        }
        
        $output = array();
        foreach ($matches[1] as $postId) {
            $output[$postId] = $post = new Post($postId);
            $post->setComments($this->_loadPostComments($post));
        }
        
        return $output;
    }

    public function setCacher(\Zend_Cache_Core $cacher)
    {
        $this->_cacher = $cacher;
        return $this;
    }
    
    public function getUrl()
    {
        return sprintf(self::POSTS_URL, $this->getId());
    }
    
    private function _loadPostComments(Post $post)
    {
        $content = $this->_cache(3600, $post->getId(), function(Profile $profile) use ($post) {
            return file_get_contents(sprintf($profile::COMMENTS_URL, $post->getId()));
        });
        
        $content = \Zend_Json::decode(trim(mb_substr($content, 5)));
        
        $post->setTitle(mb_substr($content[1][4], 0, 30) . '...');
        $post->setUrl('https://plus.google.com/' . $content[1][21]);
        
        $output = array();
        foreach (@$content[1][7] as $comment) {
            $output[] = new Comment(array(
                'authorName'  => $comment[1],
                'authorPhoto' => $comment[16],
                'text'        => $comment[2],
                'url'         => $post->getUrl(),
                'date'        => substr($comment[3], 0, 10),
            ));
        }
        
        return $output;
    }
    
    private function _cache($lifetime, $key, $callback)
    {
        $key = md5($key);
        
        if ($this->_cacher) {
            if ($result = $this->_cacher->load($key)) {
                return $result;
            }
        }
        
        $result = $callback($this);
        
        if ($this->_cacher) {
            $this->_cacher->save($result, $key);
        }
        
        return $result;
    }
}