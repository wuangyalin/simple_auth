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

    /** @ORM\Column(name="user_role", type="string", length=255, nullable=false) */
    protected $user_role;

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


    public function setUserRole($userRole) {
        $this->user_role = $userRole;
        $this->addRole($userRole);
        return $this;
    }
    
    public function getUserRole() {

        return $this->user_role;
    }
    
}