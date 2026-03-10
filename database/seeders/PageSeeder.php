<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample pages
        Page::factory()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => '<h1>Welcome to Our Website</h1><p>This is the home page content.</p>',
            'active' => true,
        ]);

        Page::factory()->create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<h1>About Us</h1><p>Learn more about our company.</p>',
            'active' => true,
        ]);

        Page::factory()->create([
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => '<h1>Contact Us</h1><p>Get in touch with us.</p>',
            'active' => true,
        ]);

        Page::factory()->create([
            'title' => 'Services',
            'slug' => 'services',
            'content' => '<h1>Our Services</h1><p>Discover what we offer.</p>',
            'active' => true,
        ]);

        // Create some additional pages using the factory
        Page::factory(5)->create();
    }
}