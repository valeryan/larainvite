<?php

namespace Junaidnasir\Larainvite\Events;

use Illuminate\Queue\SerializesModels;
use Junaidnasir\Larainvite\Models\UserInvitationInterface;

class Expired
{
    use SerializesModels;

    public $invitation;

    /**
     * Create a new event instance.
     *
     * @param $invitation
     */
    public function __construct(UserInvitationInterface $invitation)
    {
        $this->invitation = $invitation;
    }
}
