<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // <-- Importa il trait
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes; // <-- Aggiungi il trait

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'microsoft_id',
        'azure_id',
    ];

    /**
     * Get the URL to the user's profile in the admin panel.
     *
     * @return string
     */
    /**
     * Get the URL to the user's profile in the admin panel.
     *
     * @return string
     */
    public function adminlte_profile_url()
    {
        return route('profile.show');
    }

    /**
     * Get the user's profile image URL for AdminLTE.
     *
     * @return string
     */
    public function adminlte_image()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        
        // Fallback to Gravatar or a default image
        $email = $this->email ?? '';
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=160";
    }

    /**
     * Get the user's description/role for AdminLTE.
     *
     * @return string
     */
    public function adminlte_desc()
    {
        // If using Spatie Roles & Permissions
        if (method_exists($this, 'getRoleNames') && $this->roles->isNotEmpty()) {
            return $this->roles->first()->name;
        }
        
        // Fallback to a default role or empty string
        return 'User';
    }

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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
