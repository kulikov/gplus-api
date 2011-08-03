<?php

namespace Gplus;

class Post
{
    private
        $_postId = null,
        $_title = null,
        $_url = null,
        $_comments = array();
        
    public function __construct($postId)
    {
        $this->_postId = $postId;
    }
    
    public function getId()
    {
        return $this->_postId;
    }
    
    public function getTitle()
    {
        return $this->_title;
    }
    
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
    
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }
    
    public function setComments($comments)
    {
        $this->_comments = $comments;
        return $this;
    }
    
    public function getComments()
    {
        return $this->_comments;
    }
}