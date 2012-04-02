<?php

namespace Gplus;

class Post
{
    private
        $_params   = array(),
        $_comments = array();

    public function __construct(array $params)
    {
        $this->_params = $params;
    }

    public function getId()
    {
        return $this->_get('id');
    }

    public function getAuthorName()
    {
        return $this->_get('authorName');
    }

    public function getDate()
    {
        return $this->_get('date');
    }

    public function getText()
    {
        return $this->_get('text');
    }

    public function getUrl()
    {
        return $this->_get('url');
    }

    public function containsString($string)
    {
        $content = print_r(array($this->_get('allContent'), $this->getText()), true);
        return stristr($content, $string);
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


    /* PRIVATE */

    private function _get($name, $default = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $default;
    }

}
