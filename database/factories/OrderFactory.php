<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Order;

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
            'customer_id' => Customer::factory(),
            'product_id' => $this->faker->randomNumber(),
            'status' => $this->faker->randomElement(["received","urgent","ongoing","delivered"]),
            'payment_status' => $this->faker->randomElement(["paid","unpaid","initialpaid"]),
            'received_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
        ];
    }
}
