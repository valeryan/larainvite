<?php

namespace Valeryan\Larainvite;

trait UserInvitationTrait
{
    /**
     * return all invitation as laravel collection
     * @return hasMany invitation Models
     */
    public function invitations()
    {
        return $this->hasMany(config('larainvite.invitation_model'));
    }

    /**
     * return successful invitation by a user
     * @return hasMany
     */
    public function successfulInvitations()
    {
        return $this->invitations()->where('status', 'successful');
    }
    /**
     * return pending invitations by a user
     * @return hasMany
     */
    public function pendingInvitations()
    {
        return $this->invitations()->where('status', 'pending');
    }
}
