<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Media;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . '.jpg',
            'file_name' => $this->faker->word . '.jpg',
            'mime_type' => 'image/jpeg',
            'path' => 'media/' . $this->faker->uuid . '.jpg',
            'size' => $this->faker->numberBetween(1000, 5000000),
        ];
    }
}