<?php

namespace Valeryan\Larainvite;

use Carbon\Carbon;

interface InvitationInterface
{
    /**
     * Create new invitation
     * @param  string   $email      Email to invite
     * @param  int      $referral   Referral
     * @param  Carbon   $expires    Expiration Date Time
     * @return string               Referral code
     */
    public function invite($email, $referral, $expires);
    
    /**
     * Set referral code and UserInvitation instance
     * @param string $token referral Code
     */
    public function setToken($token);

    /**
     * Returns Invitation record
     * @return \Valeryan\Larainvite\Models\UserInvitation
     */
    public function get();

    /**
     * Returns invitation status
     * @return string pending | successful | expired | canceled | invalid
     */
    public function status();

    /**
     * Set invitation as successful
     * @return boolean true on success | false on error
     */
    public function consume();

    /**
     * Cancel an invitation
     * @return boolean true on success | false on error
     */
    public function cancel();

    /**
     * Resend the invitation
     * @param $expires
     * @return true
     */
    public function remind($expires);

    /**
     * check if a code exist
     *
     * @return boolean true if code found | false if not
     */
    public function isExisting();

    /**
     * check if invitation is valid
     * @return boolean
     */
    public function isValid();

    /**
     * check if invitation has expired
     * @return boolean
     */
    public function isExpired();
    
    /**
     * check if invitation status is pending
     * @return boolean
     */
    public function isPending();

    /**
     * check if given token is valid and given email is allowed
     * @param $email
     * @return bool
     */
    public function isAllowed($email);
}
