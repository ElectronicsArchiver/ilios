<?php

declare(strict_types=1);

namespace App\Tests\DataLoader;

use App\Entity\DTO\SessionTypeDTO;

class SessionTypeData extends AbstractDataLoader
{
    protected function getData(): array
    {
        $arr = [];

        $arr[] = [
            'id' => 1,
            'title' => $this->faker->text(50),
            'assessmentOption' => 1,
            'school' => 1,
            'aamcMethods' => ['AM001'],
            'sessions' => ['1', '5', '6', '7', '8'],
            'calendarColor' => $this->faker->hexColor(),
            'assessment' => false,
            'active' => false,
        ];

        $arr[] = [
            'id' => 2,
            'title' => 'second session type',
            'assessmentOption' => 2,
            'school' => 1,
            'aamcMethods' => ['AM001'],
            'sessions' => ['2', '3', '4'],
            'calendarColor' => '#0a1b2c',
            'assessment' => true,
            'active' => true,
        ];


        return $arr;
    }

    public function create(): array
    {
        return [
            'id' => 3,
            'title' => $this->faker->text(50),
            'school' => 1,
            'aamcMethods' => ['AM001'],
            'sessions' => ['1'],
            'calendarColor' => $this->faker->hexColor(),
            'assessment' => false,
            'active' => false,
        ];
    }

    public function createInvalid(): array
    {
        return [];
    }

    public function getDtoClass(): string
    {
        return SessionTypeDTO::class;
    }
}
