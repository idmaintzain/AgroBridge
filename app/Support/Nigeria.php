<?php

namespace App\Support;

class Nigeria
{
    /**
     * @return list<string>
     */
    public static function states(): array
    {
        return [
            'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue',
            'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT',
            'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi',
            'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo',
            'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara',
        ];
    }

    /**
     * @return list<string>
     */
    public static function crops(): array
    {
        return [
            'Atarodo', 'Avocado', 'Banana', 'Bitter leaf', 'Cabbage', 'Carrot', 'Cashew', 'Cassava',
            'Catfish', 'Chicken', 'Cocoa', 'Coconut', 'Cocoyam', 'Cotton', 'Cowpea', 'Cucumber',
            'Date', 'Egusi', 'Fonio', 'Garden egg', 'Garlic', 'Garri', 'Ginger', 'Groundnut',
            'Guava', 'Honey', 'Kolanut', 'Lime', 'Locust bean', 'Maize', 'Mango', 'Millet',
            'Mushroom', 'Okra', 'Oloyin', 'Onion', 'Orange', 'Palm oil', 'Pawpaw', 'Pepper',
            'Pineapple', 'Plantain', 'Potato', 'Pumpkin', 'Rice', 'Sesame', 'Shea', 'Snail',
            'Sorghum', 'Soursop', 'Soybean', 'Sugarcane', 'Sweet potato', 'Tiger nut', 'Tomato',
            'Turmeric', 'Ugwu', 'Waterleaf', 'Watermelon', 'Yam', 'Zobo',
        ];
    }
}
