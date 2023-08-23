<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Locale\Entity\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class Load010_Locale extends Fixture
{
    private EntityManagerInterface $entityManager;

    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $this->entityManager = $manager;

        /** @var array{int, int, string, string} $timezones */
        $timezones = [
            [-12, 00, 'International Date Line West', 'Etc/GMT+12'],
            [-11, 00, 'Coordinated Universal Time-11', 'Etc/GMT+11'],
            [-10, 00, 'Hawaii', 'Pacific/Honolulu'],
            [-9, 00, 'Alaska', 'America/Anchorage'],
            [-8, 00, 'Baja California', 'America/Santa_Isabel'],
            [-8, 00, 'Pacific Time (US and Canada)', 'America/Los_Angeles'],
            [-7, 00, 'Chihuahua, La Paz, Mazatlan', 'America/Chihuahua'],
            [-7, 00, 'Arizona', 'America/Phoenix'],
            [-7, 00, 'Mountain Time (US and Canada)', 'America/Denver'],
            [-6, 00, 'Central America', 'America/Guatemala'],
            [-6, 00, 'Central Time (US and Canada)', 'America/Chicago'],
            [-6, 00, 'Saskatchewan', 'America/Regina'],
            [-6, 00, 'Guadalajara, Mexico City, Monterey', 'America/Mexico_City'],
            [-5, 00, 'Bogota, Lima, Quito', 'America/Bogota'],
            [-5, 00, 'Indiana (East)', 'America/Indiana/Indianapolis'],
            [-5, 00, 'Eastern Time (US and Canada)', 'America/New_York'],
            [-4, 30, 'Caracas', 'America/Caracas'],
            [-4, 00, 'Atlantic Time (Canada)', 'America/Halifax'],
            [-4, 00, 'Asuncion', 'America/Asuncion'],
            [-4, 00, 'Georgetown, La Paz, Manaus, San Juan', 'America/La_Paz'],
            [-4, 00, 'Cuiaba', 'America/Cuiaba'],
            [-4, 00, 'Santiago', 'America/Santiago'],
            [-3, 30, 'Newfoundland', 'America/St_Johns'],
            [-3, 00, 'Brasilia', 'America/Sao_Paulo'],
            [-3, 00, 'Greenland', 'America/Godthab'],
            [-3, 00, 'Cayenne, Fortaleza', 'America/Cayenne'],
            [-3, 00, 'Buenos Aires', 'America/Argentina/Buenos_Aires'],
            [-3, 00, 'Montevideo', 'America/Montevideo'],
            [-2, 00, 'Coordinated Universal Time-2', 'Etc/GMT+2'],
            [-1, 00, 'Cape Verde', 'Atlantic/Cape_Verde'],
            [-1, 00, 'Azores', 'Atlantic/Azores'],
            [0, 00, 'Casablanca', 'Africa/Casablanca'],
            [0, 00, 'Monrovia, Reykjavik', 'Atlantic/Reykjavik'],
            [0, 00, 'Dublin, Edinburgh, Lisbon, London', 'Europe/London'],
            [0, 00, 'Coordinated Universal Time', 'Etc/GMT'],
            [1, 00, 'Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna', 'Europe/Amsterdam'],
            [1, 00, 'Brussels, Copenhagen, Madrid, Paris', 'Europe/Paris'],
            [1, 00, 'West Central Africa', 'Africa/Lagos'],
            [1, 00, 'Belgrade, Bratislava, Budapest, Ljubljana, Prague', 'Europe/Budapest'],
            [1, 00, 'Sarajevo, Skopje, Warsaw, Zagreb', 'Europe/Warsaw'],
            [1, 00, 'Windhoek', 'Africa/Windhoek'],
            [2, 00, 'Athens, Bucharest, Istanbul', 'Europe/Istanbul'],
            [2, 00, 'Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius', 'Europe/Kiev'],
            [2, 00, 'Cairo', 'Africa/Cairo'],
            [2, 00, 'Damascus', 'Asia/Damascus'],
            [2, 00, 'Amman', 'Asia/Amman'],
            [2, 00, 'Harare, Pretoria', 'Africa/Johannesburg'],
            [2, 00, 'Jerusalem', 'Asia/Jerusalem'],
            [2, 00, 'Beirut', 'Asia/Beirut'],
            [3, 00, 'Baghdad', 'Asia/Baghdad'],
            [3, 00, 'Minsk', 'Europe/Minsk'],
            [3, 00, 'Kuwait, Riyadh', 'Asia/Riyadh'],
            [3, 00, 'Nairobi', 'Africa/Nairobi'],
            [3, 30, 'Tehran', 'Asia/Tehran'],
            [4, 00, 'Moscow, St. Petersburg, Volgograd', 'Europe/Moscow'],
            [4, 00, 'Tbilisi', 'Asia/Tbilisi'],
            [4, 00, 'Yerevan', 'Asia/Yerevan'],
            [4, 00, 'Abu Dhabi, Muscat', 'Asia/Dubai'],
            [4, 00, 'Baku', 'Asia/Baku'],
            [4, 00, 'Port Louis', 'Indian/Mauritius'],
            [4, 30, 'Kabul', 'Asia/Kabul'],
            [5, 00, 'Tashkent', 'Asia/Tashkent'],
            [5, 00, 'Islamabad, Karachi', 'Asia/Karachi'],
            [5, 30, 'Sri Jayewardenepura Kotte', 'Asia/Colombo'],
            [5, 30, 'Chennai, Kolkata, Mumbai, New Delhi', 'Asia/Kolkata'],
            [5, 45, 'Kathmandu', 'Asia/Kathmandu'],
            [6, 00, 'Astana', 'Asia/Almaty'],
            [6, 00, 'Dhaka', 'Asia/Dhaka'],
            [6, 00, 'Yekaterinburg', 'Asia/Yekaterinburg'],
            [6, 30, 'Yangon', 'Asia/Yangon'],
            [7, 00, 'Bangkok, Hanoi, Jakarta', 'Asia/Bangkok'],
            [7, 00, 'Novosibirsk', 'Asia/Novosibirsk'],
            [8, 00, 'Krasnoyarsk', 'Asia/Krasnoyarsk'],
            [8, 00, 'Ulaanbaatar', 'Asia/Ulaanbaatar'],
            [8, 00, 'Beijing, Chongqing, Hong Kong, Urumqi', 'Asia/Shanghai'],
            [8, 00, 'Perth', 'Australia/Perth'],
            [8, 00, 'Kuala Lumpur, Singapore', 'Asia/Singapore'],
            [8, 00, 'Taipei', 'Asia/Taipei'],
            [9, 00, 'Irkutsk', 'Asia/Irkutsk'],
            [9, 00, 'Seoul', 'Asia/Seoul'],
            [9, 00, 'Osaka, Sapporo, Tokyo', 'Asia/Tokyo'],
            [9, 30, 'Darwin', 'Australia/Darwin'],
            [9, 30, 'Adelaide', 'Australia/Adelaide'],
            [10, 00, 'Hobart', 'Australia/Hobart'],
            [10, 00, 'Yakutsk', 'Asia/Yakutsk'],
            [10, 00, 'Brisbane', 'Australia/Brisbane'],
            [10, 00, 'Guam, Port Moresby', 'Pacific/Port_Moresby'],
            [10, 00, 'Canberra, Melbourne, Sydney', 'Australia/Sydney'],
            [11, 00, 'Vladivostok', 'Asia/Vladivostok'],
            [11, 00, 'Solomon Islands, New Caledonia', 'Pacific/Guadalcanal'],
            [12, 00, 'Coordinated Universal Time+12', 'Etc/GMT-12'],
            [12, 00, 'Fiji, Marshall Islands', 'Pacific/Fiji'],
            [12, 00, 'Magadan', 'Asia/Magadan'],
            [12, 00, 'Auckland, Wellington', 'Pacific/Auckland'],
            [13, 00, 'Nuku\'alofa', 'Pacific/Tongatapu'],
            [13, 00, 'Samoa', 'Pacific/Apia'],
        ];

        foreach ($timezones as [$hours, $minutes, $name, $key])
        {
            $timezone = $this->createTimeZone($hours, $minutes, $name);
            $this->addReference('timezone_'.$key, $timezone);

            $this->entityManager->persist($timezone);
        }

        $manager->flush();
    }

    private function createTimeZone(int $offsetHours, int $offsetMinutes, string $name): Timezone
    {
        return (new Timezone())
            ->setOffsetHours($offsetHours)
            ->setOffsetMinutes($offsetMinutes)
            ->setName($name);
    }
}
