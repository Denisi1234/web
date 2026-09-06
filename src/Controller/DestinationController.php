<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Destination Controller - Tanzanian Travel Hubs & Stays
 */
class DestinationController extends AppController
{
    public function detail($slug = null)
    {
        $trips = [
            [
                'id' => 1,
                'img' => 'assets/img/city/ct-6.png', 
                'title' => 'Zanzibar Beach Escape & Stone Town Stays', 
                'price' => '350000', 
                'for' => 'Per Night / 2 Guests',
                'city' => 'Zanzibar' 
            ],
            [
                'id' => 2,
                'img' => 'assets/img/city/ct-12.png', 
                'title' => 'Serengeti Safari & Luxury Camp Retreat', 
                'price' => '520000', 
                'for' => 'Per Night / 2 Guests',
                'city' => 'Serengeti' 
            ],
            [
                'id' => 3,
                'img' => 'assets/img/city/ct-9.png', 
                'title' => 'Mount Meru & Arusha National Park Stays', 
                'price' => '220000', 
                'for' => 'Per Night / 2 Guests',
                'city' => 'Arusha' 
            ],
            [
                'id' => 4,
                'img' => 'assets/img/city/ct-8.png', 
                'title' => 'Dar es Salaam Executive & Beachfront Lodges', 
                'price' => '160000', 
                'for' => 'Per Night / 2 Guests',
                'city' => 'Dar es Salaam' 
            ],
            [
                'id' => 5,
                'img' => 'assets/img/city/c-4.png', 
                'title' => 'Mount Kilimanjaro Base Camp & Nature Lodges', 
                'price' => '280000', 
                'for' => 'Per Night / 2 Guests',
                'city' => 'Kilimanjaro' 
            ],
            [
                'id' => 6,
                'img' => 'assets/img/city/c-7.png', 
                'title' => 'Pemba Island Coral Reef Eco Resort', 
                'price' => '390000', 
                'for' => 'Per Night / 2 Guests',
                'city' => 'Pemba' 
            ],
        ];

        $article = $trips[0]; // Default value

        if ($slug !== null) {
            foreach ($trips as $item) {
                $slugifiedTitle = strtolower(str_replace(' ', '-', $item['title']));
                if ($slugifiedTitle === strtolower($slug) || strtolower($item['city']) === strtolower($slug)) {
                    $article = $item;
                    break;
                }
            }
        }

        $this->set(compact('article', 'trips'));
        $this->render('/Pages/destination-detail');
    }
}