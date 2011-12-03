<?php

namespace Gplus;

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

    public function getUrl()
    {
        return Api::GPLUS_URL . $this->getId();
    }
}