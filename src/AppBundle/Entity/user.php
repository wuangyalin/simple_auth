<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class user extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(name="googleplus_id", type="string", length=255, nullable=true) */
    protected $googleplus_id;

    /** @ORM\Column(name="googleplus_picture", type="string", length=255, nullable=true) */
    protected $googleplus_picture;

    /** @ORM\Column(name="full_name", type="string", length=255, nullable=true) */
    protected $fullName;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }


    public function setGoogleplusId($googlePlusId) {
        $this->googleplus_id = $googlePlusId;
    
        return $this;
    }
    
    public function getGoogleplusId() {
        return $this->googleplus_id;
    }

    public function setgoogleplus_picture($googlePlusPicture) {
        $this->googleplus_picture = $googlePlusPicture;
    
        return $this;
    }
    
    public function getgoogleplus_picture() {
        return $this->googleplus_picture;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;

        return $this;
    }
}