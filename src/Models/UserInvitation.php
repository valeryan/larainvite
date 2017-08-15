<?php

namespace Valeryan\Larainvite\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserInvitation
 *
 * @property int id
 * @property string token
 * @property string email
 * @property int referrer_id
 * @property string status
 * @property string valid_till
 * @property string created_at
 * @property string updated_at
 *
 */

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
    public function referrer()
    {
        return $this->belongsTo(config('larainvite.user_model'), 'referrer_id');
    }
}
