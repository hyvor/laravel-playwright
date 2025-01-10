<?php

namespace Hyvor\LaravelPlaywright\Tests\Helpers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 */
class UserModel extends Model
{

    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    protected $table = 'users';

    public static function newFactory(): UserFactory
    {
        return new UserFactory();
    }

}