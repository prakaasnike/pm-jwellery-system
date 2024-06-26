<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\;
use App\Models\Product;
use App\Models\Unit;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'product_image' => $this->faker->word(),
            'name' => $this->faker->name(),
            'stone_name' => $this->faker->word(),
            'stone_weight' => $this->faker->randomFloat(0, 0, 9999999999.),
            'product_net_weight' => $this->faker->randomFloat(0, 0, 9999999999.),
            'product_total_weight' => $this->faker->randomFloat(0, 0, 9999999999.),
            'unit_id' => Unit::factory(),
            'category_id' => ::factory(),
            'type_id' => $this->faker->randomNumber(),
        ];
    }
}
