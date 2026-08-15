<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed','ai_credits'=>'integer','is_admin'=>'boolean']; }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function creditTransactions(): HasMany { return $this->hasMany(CreditTransaction::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function featureRequests(): HasMany { return $this->hasMany(FeatureRequest::class); }
    public function activeSubscription(): HasOne { return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany(); }
}
