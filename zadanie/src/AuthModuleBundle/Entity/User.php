<?php

namespace AuthModuleBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $isConfirmed;

    /**
     * @var string
     */
    private $password;

    /**
     * @var \DateTime
     */
    private $birthday;

    /**
     * @var \DateTime
     */
    private $registered;

    /**
     * @var string
     */
    private $token;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isConfirmed
     *
     * @param boolean $isConfirmed
     *
     * @return User
     */
    public function setIsConfirmed($isConfirmed)
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    /**
     * Get isConfirmed
     *
     * @return bool
     */
    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set registered
     *
     * @param \DateTime $registered
     *
     * @return User
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;

        return $this;
    }

    /**
     * Get registered
     *
     * @return \DateTime
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isConfirmed;
    }

    // serialize and unserialize must be updated - see below
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->isConfirmed
        ));
    }
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->isConfirmed
            ) = unserialize($serialized);
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    /**
     * Get email for auth process reasons called username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    private function getTokenSalt () {
        return '$#^^&$#$#@$e4j9#@$@&*';
    }

    public function getConfirmationToken() {
        return base64_encode(time().$this->getTokenSalt().$this->getId());
    }

}

