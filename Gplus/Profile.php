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
}