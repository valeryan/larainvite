<?php

namespace Valeryan\Larainvite;

use Exception;
use Carbon\Carbon;

/**
* User Invitation class
*/
class UserInvitation
{
    private $interface;
    
    public function __construct(InvitationInterface $interface)
    {
        $this->interface = $interface;
    }

    public function invite($email, $referral, $expires = null, $beforeSave = null)
    {
        $this->validateEmail($email);
        $expires = (is_null($expires)) ? Carbon::now()->addHour(config('larainvite.expires')) : $expires;
        return $this->interface->invite($email, $referral, $expires, $beforeSave);
    }

    public function get($token)
    {
        return $this->interface->setToken($token)->get();
    }

    public function status($token)
    {
        return $this->interface->setToken($token)->status();
    }

    public function isValid($token)
    {
        return $this->interface->setToken($token)->isValid();
    }

    public function isExpired($token)
    {
        return $this->interface->setToken($token)->isExpired();
    }

    public function isPending($token)
    {
        return $this->interface->setToken($token)->isPending();
    }

    public function isAllowed($token, $email)
    {
        return $this->interface->setToken($token)->isAllowed($email);
    }

    public function consume($token)
    {
        return $this->interface->setToken($token)->consume();
    }

    public function cancel($token)
    {
        return $this->interface->setToken($token)->cancel();
    }

    public function reminder($token)
    {
        return $this->interface->setToken($token)->reminder();
    }

    public function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid Email Address", 1);
        }
        return $this;
    }
}
