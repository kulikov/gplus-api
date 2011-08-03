<?php

namespace Gplus;

class Comment
{
    private
        $_property = array();
        
    public function __construct($property)
    {
        $this->_property = $property;
    }
    
    public function getTitle()
    {
        $text = strip_tags($this->getText());
        return substr($text, 0, 31);
    }
    
    public function getDate()
    {
        return $this->_get('date');
    }
    
    public function getUrl()
    {
        return $this->_get('url');
    }
    
    public function getText()
    {
        return $this->_get('text');
    }
    
    public function getAuthorName()
    {
        return $this->_get('authorName');
    }
    
    public function getAuthorPhoto()
    {
        return $this->_get('authorPhoto');
    }
    
    private function _get($name)
    {
        return isset($this->_property[$name]) ? $this->_property[$name] : null;
    }
}