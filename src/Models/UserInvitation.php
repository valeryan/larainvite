<?php

namespace Junaidnasir\Larainvite\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model implements UserInvitationInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('larainvite.table_name'));
    }

    /**
     * Referral User
     */
    public function user()
    {
        return $this->belongsTo(config('larainvite.user_model'), 'referrer_id');
    }
}
