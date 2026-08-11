<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'status',
        'otp',
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function privileges()
    {
        return $this->belongsToMany(Privilege::class, 'user_privileges');
    }

    public function hasPrivilege($routeName)
    {
        if (session()->has('staff_id')) {
            $staff = \App\Models\Staff::find(session('staff_id'));
            if ($staff && $staff->privileges) {
                $staffPrivileges = explode(',', $staff->privileges);
                $privilege = \App\Models\Privilege::where('route_name', $routeName)->first();
                return $privilege && in_array($privilege->id, $staffPrivileges);
            }
            return false;
        }

        if (in_array($this->type, ['admin', 'shop', 'seller'])) {
            return true;
        }

        return $this->privileges()->where('route_name', $routeName)->exists();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
