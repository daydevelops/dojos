<?php

namespace Database\Factories;

use App\Models\StripeProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class StripeProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StripeProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id'=>$this->faker->uuid,
            'description' => $this->faker->sentence
        ];
    }
}
