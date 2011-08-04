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

    public function getDate()
    {
        return $this->_get('date');
    }

    public function getFormatedDate()
    {
        $date = $this->_get('date');
        $d    = date('d.m.Y', $date);

        if ($d == date('d.m.Y')) {
            return date('H:i', $date);
        } elseif ($d == date('d.m.Y', strtotime('-1 day'))) {
            return 'Вчера в ' . date('H:i', $date);
        }

        return date('d.m.Y H:i', $date);
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

    public function getAuthorProfileUrl()
    {
        return Api::GPLUS_URL . $this->_get('authorId');
    }

    private function _get($name)
    {
        return isset($this->_property[$name]) ? $this->_property[$name] : null;
    }
}