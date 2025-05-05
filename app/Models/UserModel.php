<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject; // implementasi class Authenticatable
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserModel extends Authenticatable implements JWTSubject
{


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $table = 'm_user';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'level_id',
        'username',
        'nama',
        'password',
        'image',
        'profil_images',
        'created_at',
        'updated_at'
    ];

    protected $hidden = ['password']; // jangan ditampilkan saat select
    protected $casts = ['password' => 'hashed']; // casting password agar otomatis di hash

    public function getAuthIdentifierName()
    {
        return 'username'; // default 'email', tapi kamu pakai username
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LevelModel::class, 'level_id', 'level_id');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => url('/storage/posts/' . $image),
        );
    }


    public function getRole()
    {
        return $this->level->level_kode;
    }
    public function getRoleName(): string
    {
        return $this->level->level_nama;
    }

    public function hasRole($role): bool
    {
        return $this->level->level_kode == $role;
    }
}
