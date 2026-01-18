<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productName = fake()->sentence();
        $urlEncodedProductName = urlencode($productName);
        return [
            'user_id' => 1,
            'name' => $productName,
            'description' => fake()->text(),
            'price' => fake()->numberBetween(10, 20) * 500,
            'image_url' => "https://dummyimage.com/640x480/000/fff.png&text=$urlEncodedProductName",
        ];
    }
}
