<?php

namespace Valeryan\Larainvite\Events;

use Illuminate\Queue\SerializesModels;
use Valeryan\Larainvite\Models\UserInvitationInterface;

class Invited
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
