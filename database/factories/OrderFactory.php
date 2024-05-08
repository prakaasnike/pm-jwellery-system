<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\Status;
use App\Models\Customer; // Assuming the Customer model is used for the customer_id field
use App\Models\Product; // Assuming the Product model is used for the product_id field
use App\Models\Payment; // Assuming the Payment model is used for the payment_id field

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
            'product_id' => Product::factory(),
            'status_id' => Status::factory(),
            'payment_id' => Payment::factory(),
            'received_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
        ];
    }
}
