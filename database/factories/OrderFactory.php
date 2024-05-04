<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
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
            'customer_id' => $this->faker->randomNumber(),
            'product_id' => $this->faker->randomNumber(),
            'status_id' => Status::factory(),
            'payment_id' => $this->faker->randomNumber(),
            'Received_date' => $this->faker->date(),
            'Delivery_date' => $this->faker->date(),
        ];
    }
}
