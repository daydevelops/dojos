<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Dojo;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class DojoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dojo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word . " " . $this->faker->word,
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'location' => $this->faker->sentence,
            'price' => "$" . rand(20, 40) . "/month",
            'contact' => $this->faker->sentence,
            'classes' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];
    }
}
