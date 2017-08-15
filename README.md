# larainvite
User (signup) invitation package for laravel


[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/junaidnasir/larainvite/master/LICENSE.txt)
[![Total Downloads](https://poser.pugx.org/junaidnasir/larainvite/downloads)](https://packagist.org/packages/junaidnasir/larainvite)

larainvite is a ***laravel*** package, to allow existing users to invite others by email.

It generates referral token and keep track of status.


## Installation

Begin by installing the package through Composer. Run the following command in your terminal:

```bash
composer require valeryan/larainvite
```

add the package service provider in the providers array in `config/app.php`:

```php
Valeryan\Larainvite\LaraInviteServiceProvider::class
```

you may add the facade access in the aliases array:

```php
'Invite'  => Valeryan\Larainvite\Facades\Invite::class
```

publish the migration and config file:

```bash
php artisan vendor:publish --provider="Valeryan\Larainvite\LaraInviteServiceProvider"
```

migrate to create `user_invitation` table

```bash
php artisan migrate
```

edit your `User` model to include `larainviteTrait`
```php
use Valeryan\Larainvite\Traits\UserInvitationTrait;
class user ... {
    use UserInvitationTrait;
}
```


## Usage

You can use ***facade accessor*** to retrieve the package controller. Examples:

```php
$user = Auth::user();
//Invite::invite(EMAIL, REFERRAL_ID); 
$token = Invite::invite('email@address.com', $user->id);
//or 
//Invite::invite(EMAIL, REFERRAL_ID, EXPIRATION); 
$token = Invite::invite('email@address.com', $user->id, '2016-12-31 10:00:00');
//or
//Invite::invite(EMAIL, REFERRAL_ID, EXPIRATION, BEFORE_SAVE_CALLBACK); 
$token = Invite::invite($to, Auth::user()->id, Carbon::now()->addYear(1),
                      function(/* InvitationModel, see Configurations */ $invitation) use ($someValue) {
      $invitation->someParam = $someValue;
});
```

now create routes with the `token`, when user access that route you can use following methods
```php
// Get route
$token = Request::input('token');
if( Invite::isValid($token))
{
    $invitation = Invite::get($token); //retrieve invitation modal
    $invited_email = $invitation->email;
    $referral_user = $invitation->user;

    // show signup form
} else {
    $status = Invite::status($token);
    // show error or show simple signup form
}
```
```php
// Post route
$token = Request::input('token');
$email = Request::input('signup_email');
if( Invite::isAllowed($token, $email) ){
    // Register this user
    Invite::consume($token);
} else {
    // either token is inavalid, or provided email was not invited against this token
}
```
with help of new trait you have access to invitations sent by user
```php
$user= User::find(1);
$invitations = $user->invitations;
$count = $user->invitations()->count();
```
## Events

***larainvite*** fires several [events](https://laravel.com/docs/master/events)

*  'Valeryan\Larainvite\Invited' 
*  'Valeryan\Larainvite\Consumed' 
*  'Valeryan\Larainvite\Canceled' 
*  'Valeryan\Larainvite\Expired' 

all of these events include `invitation modal` so you can listen to these events.
include listener in `EventServiceProvider.php`
```php
protected $listen = [
    'Valeryan\Larainvite\Invited' => [
        'App\Listeners\UserInvited',
    ],
];
```
`UserInvited.php`
```php
public function handle($event)
{
    $invitaton = $event->invitation
    \Mail::queue('invitations.emailBody', $invitation, function ($m) use ($invitation) {
            $m->from('From Address', 'Your App Name');
            $m->to($invitation->email);
            $m->subject("You have been invited by ". $invitation->user->name);
        });
}
```

## Configurations

in `config/larainvite.php` you can set default expiration time in hours from current time.

```php
'expires' => 48
```

you can also change the table name to be used, in `larainvite.php`
```php
'table_name' => 'user_invitations'
```

you can also change user model to be used, in `larainvite.php`
```php
'user_model' => 'App\User'
```

you can also change invitation model to be used, in `larainvite.php`
```php
'invitation_model' => 'App\Invitation'
```
