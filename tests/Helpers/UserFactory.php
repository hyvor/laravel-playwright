<?php

namespace Hyvor\LaravelPlaywright\Tests\Helpers;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserModel>
 */
class UserFactory extends Factory
{

    protected $model = UserModel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }

}