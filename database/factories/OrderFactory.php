<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\;
use App\Models\Order;
use App\Models\Status;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'order_name' => $this->faker->word(),
            'order_image' => $this->faker->word(),
            'customer_id' => ::factory(),
            'product_id' => $this->faker->randomNumber(),
            'status_id' => Status::factory(),
            'payment_id' => ::factory(),
            'received_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
        ];
    }
}
