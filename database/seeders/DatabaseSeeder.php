<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * The demo catalogue, grouped by category name.
     *
     * Each category carries four enabled products and one disabled one, so the
     * dashboard's status filter and the catalogue's "enabled only" rule both
     * have something to show straight after seeding.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private const CATALOGUE = [
        'Electronics' => [
            [
                'name' => 'Sony WH-1000XM5 Wireless Headphones',
                'description' => 'Over-ear noise cancelling headphones with 30-hour battery life and multipoint Bluetooth pairing.',
                'price' => 1799.00,
                'stock' => 24,
                'enabled' => true,
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'description' => 'Ergonomic wireless mouse with an 8K DPI sensor and near-silent switches.',
                'price' => 449.00,
                'stock' => 41,
                'enabled' => true,
            ],
            [
                'name' => 'Samsung T7 Portable SSD 1TB',
                'description' => 'USB 3.2 external solid-state drive reading up to 1,050 MB/s in a shock-resistant alloy case.',
                'price' => 399.00,
                'stock' => 73,
                'enabled' => true,
            ],
            [
                'name' => 'Anker 737 Power Bank 24,000mAh',
                'description' => '140W two-way fast charging power bank with a smart display, sized for laptops and phones.',
                'price' => 599.00,
                'stock' => 58,
                'enabled' => true,
            ],
            [
                'name' => 'GoPro HERO11 Black',
                'description' => 'Waterproof action camera shooting 5.3K60 video with HyperSmooth 5.0 stabilisation.',
                'price' => 2199.00,
                'stock' => 0,
                'enabled' => false,
            ],
        ],

        'Home & Kitchen' => [
            [
                'name' => 'Instant Pot Duo 7-in-1 (6 Quart)',
                'description' => 'Electric pressure cooker that also slow cooks, steams, sautés, and makes rice and yoghurt.',
                'price' => 549.00,
                'stock' => 32,
                'enabled' => true,
            ],
            [
                'name' => 'Lodge 10.25" Cast Iron Skillet',
                'description' => 'Pre-seasoned cast iron pan safe on induction, gas, and open flame, oven rated to 500°F.',
                'price' => 189.00,
                'stock' => 88,
                'enabled' => true,
            ],
            [
                'name' => 'OXO Good Grips Salad Spinner',
                'description' => 'One-handed pump spinner with a non-slip base and a bowl that doubles as a serving dish.',
                'price' => 169.00,
                'stock' => 64,
                'enabled' => true,
            ],
            [
                'name' => 'Brita Everyday Water Filter Pitcher',
                'description' => '10-cup BPA-free pitcher that cuts chlorine taste and odour, with a filter change indicator.',
                'price' => 159.00,
                'stock' => 45,
                'enabled' => true,
            ],
            [
                'name' => 'Breville Barista Express Espresso Machine',
                'description' => 'Semi-automatic espresso machine with an integrated conical burr grinder and steam wand.',
                'price' => 2999.00,
                'stock' => 0,
                'enabled' => false,
            ],
        ],

        'Books' => [
            [
                'name' => 'Atomic Habits — James Clear',
                'description' => 'A practical framework for building good habits and breaking bad ones through small changes.',
                'price' => 69.90,
                'stock' => 120,
                'enabled' => true,
            ],
            [
                'name' => 'The Pragmatic Programmer (20th Anniversary Edition)',
                'description' => 'Andrew Hunt and David Thomas on the craft of software development, fully revised.',
                'price' => 199.90,
                'stock' => 37,
                'enabled' => true,
            ],
            [
                'name' => 'Sapiens: A Brief History of Humankind',
                'description' => 'Yuval Noah Harari traces how Homo sapiens came to dominate the planet.',
                'price' => 79.90,
                'stock' => 54,
                'enabled' => true,
            ],
            [
                'name' => 'Thinking, Fast and Slow — Daniel Kahneman',
                'description' => 'The two systems that drive how we think, from the Nobel-winning psychologist.',
                'price' => 74.90,
                'stock' => 29,
                'enabled' => true,
            ],
            [
                'name' => 'Clean Code — Robert C. Martin',
                'description' => 'A handbook of agile software craftsmanship, with case studies in refactoring.',
                'price' => 189.90,
                'stock' => 0,
                'enabled' => false,
            ],
        ],

        'Sports & Outdoors' => [
            [
                'name' => 'Hydro Flask 32 oz Wide Mouth Bottle',
                'description' => 'Vacuum-insulated stainless steel bottle keeping drinks cold 24 hours and hot up to 12.',
                'price' => 215.00,
                'stock' => 76,
                'enabled' => true,
            ],
            [
                'name' => 'Garmin Forerunner 265 GPS Running Watch',
                'description' => 'AMOLED running watch with multi-band GPS, training readiness, and 13-day battery life.',
                'price' => 1699.00,
                'stock' => 18,
                'enabled' => true,
            ],
            [
                'name' => 'Coleman Sundome 4-Person Tent',
                'description' => 'Dome tent with WeatherTec floor welding and a rainfly, pitched in about ten minutes.',
                'price' => 389.00,
                'stock' => 31,
                'enabled' => true,
            ],
            [
                'name' => 'Manduka PRO Yoga Mat (6mm)',
                'description' => 'High-density closed-cell mat with a dry-grip surface, backed by a lifetime guarantee.',
                'price' => 650.00,
                'stock' => 22,
                'enabled' => true,
            ],
            [
                'name' => 'Wilson Evolution Indoor Basketball',
                'description' => 'Official size 7 game ball with a microfibre composite cover and cushion core carcass.',
                'price' => 329.00,
                'stock' => 0,
                'enabled' => false,
            ],
        ],
    ];

    /**
     * Seed one administrator, one ordinary customer, and the demo catalogue.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        User::factory()->customer()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        foreach (self::CATALOGUE as $categoryName => $products) {
            Category::create(['name' => $categoryName])
                ->products()
                ->createMany($products);
        }
    }
}
