<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'unit_id',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    /**
     * Determine if the user has verified their email address.
     * Admins bypass email verification.
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->isAdmin() || !is_null($this->email_verified_at);
    }

    /**
     * Check if the user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->is_admin === 1;
    }

    /**
     * Get the unit that the user belongs to
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Check if user has admin unit assigned
     */
    public function hasAdminUnit(): bool
    {
        return $this->unit_id && $this->unit_id === Unit::ADMIN_UNIT_ID;
    }

    /**
     * Documents received by this user
     */
    public function receivedDocuments()
    {
        return $this->hasMany(Document::class, 'received_by');
    }

    /**
     * Documents rejected by this user
     */
    public function rejectedDocuments()
    {
        return $this->hasMany(Document::class, 'rejected_by');
    }

    /**
     * Send the password reset notification using the custom email template.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
