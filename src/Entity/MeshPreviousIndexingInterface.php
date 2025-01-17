<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\IdentifiableEntityInterface;
use App\Traits\StringableEntityInterface;

/**
 * Interface MeshPreviousIndexingInterface
 */
interface MeshPreviousIndexingInterface extends
    IdentifiableEntityInterface,
    StringableEntityInterface
{
    public function setDescriptor(MeshDescriptorInterface $descriptor);

    public function getDescriptor(): MeshDescriptorInterface;

    /**
     * @param string $previousIndexing
     */
    public function setPreviousIndexing($previousIndexing);

    public function getPreviousIndexing(): string;
}
