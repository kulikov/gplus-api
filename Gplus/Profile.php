<?php

namespace Gplus;

require_once 'Post.php';
require_once 'Comment.php';

class Profile
{
    private $_profileId = null;

    public function __construct($profileId)
    {
        $this->_profileId = $profileId;
    }

    public function getId()
    {
        return $this->_profileId;
    }
}