<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Auth\Passwords\CanResetPassword;
    use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // OAuth provider fields (optional if you run the migration)
        'provider',
        'provider_id',
        'avatar',
        // optional contact and flags
        'phone',
        'terms_accepted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // Removed incorrect method-style casts definition (was causing static analysis errors).
    // Use the $casts property below instead.

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // Leave password out of automatic casts. We handle hashing with a mutator below
    ];

    /**
     * Ensure passwords are hashed when set — but don't double-hash already-hashed values.
     */
    public function setPasswordAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['password'] = $value;
            return;
        }

        // If the value already appears to be a password hash (bcrypt/argon), keep it as-is.
        // Otherwise hash the plain-text password.
        if (is_string($value) && preg_match('/^\$2[aby]\$|^\$argon2/i', $value)) {
            $this->attributes['password'] = $value;
            return;
        }

        // Otherwise assume plain text and hash it.
        $this->attributes['password'] = Hash::make($value);
    }
}
