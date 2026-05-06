<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $thai = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ญ', 'ด', 'ต', 'ถ', 'ท', 'น', 'บ', 'ป', 'ผ', 'ฝ', 'พ', 'ฟ', 'ม', 'ย', 'ร', 'ล', 'ว', 'ศ', 'ษ', 'ส', 'ห', 'อ', 'ฮ'];

        return [
            'p_id' => $this->faker->unique()->numberBetween(1, 1000000),
            'hn' => $this->faker->unique()->randomElement($thai) . rand(100000, 999999),
            'title_name' => $this->faker->title,
            'fname' => $this->faker->firstName,
            'lname' => $this->faker->lastName,
            'hospital_name' => $this->faker->company,
        ];
    }
}
