<?php

declare(strict_types=1);

namespace App\Tests\DataLoader;

use App\Entity\AlertChangeTypeInterface;
use App\Entity\DTO\AlertChangeTypeDTO;

class AlertChangeTypeData extends AbstractDataLoader
{
    protected function getData(): array
    {
        $arr = [];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_TIME,
            'title' => $this->faker->text(25),
            'alerts' => ['1', '2']
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_LOCATION,
            'title' => 'second title',
            'alerts' => []
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_LEARNING_MATERIAL,
            'title' => $this->faker->text(25),
            'alerts' => []
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_INSTRUCTOR,
            'title' => $this->faker->text(25),
            'alerts' => []
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_COURSE_DIRECTOR,
            'title' => $this->faker->text(25),
            'alerts' => []
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_LEARNER_GROUP,
            'title' => $this->faker->text(25),
            'alerts' => []
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_NEW_OFFERING,
            'title' => $this->faker->text(25),
            'alerts' => []
        ];

        $arr[] = [
            'id' => AlertChangeTypeInterface::CHANGE_TYPE_SESSION_PUBLISH,
            'title' => $this->faker->text(25),
            'alerts' => []
        ];

        return $arr;
    }

    public function create(): array
    {
        return [
            'id' => 9,
            'title' => $this->faker->text(10),
            'alerts' => ['1']
        ];
    }

    public function createInvalid(): array
    {
        return [
            'id' => $this->faker->text(),
            'alerts' => [424524]
        ];
    }

    public function getDtoClass(): string
    {
        return AlertChangeTypeDTO::class;
    }
}
