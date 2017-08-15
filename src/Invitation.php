<?php

namespace Valeryan\Larainvite;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Valeryan\Larainvite\Exceptions\InvalidTokenException;
use Valeryan\Larainvite\Exceptions\InviteNotInitializedException;

/**
*   Laravel Invitation class
*/
class Invitation implements InvitationInterface
{
    /**
     * Email address to invite
     * @var string
     */
    private $email;

    /**
     * Referral token for invitation
     * @var string
     */
    private $token = null;

    /**
     * Status of token existing in DB
     * @var bool
     */
    private $exist = false;

    /**
     * integer ID of referral
     * @var [type]
     */
    private $referral;

    /**
     * DateTime of referral token expiration
     * @var Carbon
     */
    private $expires;

    /**
     * Invitation Model
     * @var \Valeryan\Larainvite\Models\UserInvitation
     */
    private $instance;
    
    /**
     * {@inheritdoc}
     */
    public function invite($email, $referral, $expires, $beforeSave = null)
    {
        $this->readyPayload($email, $referral, $expires)
             ->createInvite($beforeSave)
             ->publishEvent('Invited');
        return $this->token;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setToken($token)
    {
        $this->token = $token;
        try {
            $this->getModelInstance(false);
        } catch (InvalidTokenException $exception) {
            // handle invalid tokens
            $this->exist = false;
        }
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $this->checkInstance();
        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function status()
    {
        if ($this->isValid()) {
            return $this->instance->status;
        }
        return 'Invalid';
    }
    
    /**
     * {@inheritdoc}
     */
    public function consume()
    {
        if ($this->isValid()) {
            $this->instance->status = 'successful';
            $this->instance->save();
            $this->publishEvent('Consumed');
            return true;
        }
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        if ($this->isValid()) {
            $this->instance->status = 'canceled';
            $this->instance->save();
            $this->publishEvent('Canceled');
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function remind($expires)
    {
        $this->checkInstance();
        $this->instance->valid_till = $expires;
        $this->instance->save();
        $this->publishEvent('Invited');
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isExisting()
    {
        $this->checkInstance();
        return $this->exist;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return (!$this->isExpired() && $this->isPending());
    }
    
    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        if (!$this->isExisting()) {
            return true;
        }
        if (strtotime($this->instance->valid_till) >= time()) {
            return false;
        }
        $this->instance->status = 'expired';
        $this->instance->save();
        $this->publishEvent('Expired');
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        if (!$this->isExisting()) {
            return false;
        }
        return ($this->instance->status == 'pending') ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($email)
    {
        return ($this->isValid() && ($this->instance->email == $email));
    }

    /**
     * @throws InviteNotInitializedException
     */
    private function checkInstance()
    {
        if (is_null($this->instance)) {
            throw new InviteNotInitializedException('No Token is defined. You must call setToken() before using this method. ');
        }
    }
    /**
     * generate invitation token and call save
     * @param null|mixed $beforeSave
     * @return self
     */
    private function createInvite($beforeSave = null)
    {
        $token = md5(uniqid());
        return $this->save($token, $beforeSave);
    }

    /**
     * saves invitation in DB
     * @param  string $token referral token
     * @param null|mixed $beforeSave
     * @return self
     */
    private function save($token, $beforeSave = null)
    {
        $this->getModelInstance();
        $this->instance->email          = $this->email;
        $this->instance->referrer_id    = $this->referral;
        $this->instance->valid_till     = $this->expires;
        $this->instance->token          = $token;

        if (!is_null($beforeSave)) {
            if ($beforeSave instanceof Closure) {
                $beforeSave->call($this->instance);
            } elseif (is_callable($beforeSave)) {
                call_user_func($beforeSave, $this->instance);
            }
        }
        $this->instance->save();

        $this->token  = $token;
        $this->exist  = true;
        return $this;
    }

    /**
     * set $this->instance to Valeryan\Larainvite\Models\UserInvitation instance
     * @param  boolean $allowNew allow new model
     * @throws InvalidTokenException
     * @return Invitation
     */
    private function getModelInstance($allowNew = true)
    {
        $model = config('larainvite.invitation_model');
        //if (is_null($this->token) && $allowNew) {
        if ($allowNew) {
            $this->instance = new $model;
            return $this;
        }
        try {
            $this->instance = (new $model)->where('token', $this->token)->firstOrFail();
            $this->exist    = true;
            return $this;
        } catch (ModelNotFoundException $e) {
            throw new InvalidTokenException("Invalid Token {$this->token}", 401);
        }
    }

    /**
     * set input variables
     * @param  string   $email    email to invite
     * @param  integer  $referral referral id
     * @param  Carbon $expires  expiration of token
     * @return self
     */
    private function readyPayload($email, $referral, $expires)
    {
        $this->email    = $email;
        $this->referral = $referral;
        $this->expires  = $expires;
        return $this;
    }

    /**
     * Fire Laravel event
     * @param  string $event event name
     * @return self
     */
    private function publishEvent($event)
    {
        $event_name = '\\Valeryan\\Larainvite\\Events\\' . $event;
        Event::fire(new $event_name($this->instance));
        return $this;
    }
}
