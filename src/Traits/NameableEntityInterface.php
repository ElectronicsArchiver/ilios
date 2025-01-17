<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Interface NameableEntityInterface
 */
interface NameableEntityInterface
{
    /**
     * @param string $name
     */
    public function setName($name);

    public function getName(): string;
}
