<?php

declare(strict_types=1);

namespace App\Entity\DTO;

use App\Attribute as IA;

/**
 * Class SessionObjectiveDTO
 */
#[IA\DTO('sessionObjectives')]
#[IA\ExposeGraphQL]
class SessionObjectiveDTO
{
    #[IA\Id]
    #[IA\Expose]
    #[IA\Type('integer')]
    public int $id;

    #[IA\Expose]
    #[IA\Type('string')]
    public string $title;

    #[IA\Expose]
    #[IA\Type('boolean')]
    public bool $active;

    #[IA\Expose]
    #[IA\Related('sessions')]
    #[IA\Type('integer')]
    public int $session;

    #[IA\Expose]
    #[IA\Type('integer')]
    public int $position;

    /**
     * @var int[]
     */
    #[IA\Expose]
    #[IA\Related]
    #[IA\Type('array<string>')]
    public array $terms = [];

    /**
     * Needed for Voting, not exposed in the API
     */
    #[IA\Type('integer')]
    public int $course;

    /**
     * Needed for Voting, not exposed in the API
     */
    #[IA\Type('integer')]
    public int $school;

    /**
     * Needed for Voting, not exposed in the API
     */
    #[IA\Type('boolean')]
    public bool $courseIsLocked;

    /**
     * Needed for Voting, not exposed in the API
     */
    #[IA\Type('boolean')]
    public bool $courseIsArchived;

    /**
     * @var int[]
     */
    #[IA\Expose]
    #[IA\Related('courseObjectives')]
    #[IA\Type('array<string>')]
    public array $courseObjectives = [];

    /**
     * @var int[]
     */
    #[IA\Expose]
    #[IA\Related]
    #[IA\Type('array<string>')]
    public array $meshDescriptors = [];

    #[IA\Expose]
    #[IA\Related('sessionObjectives')]
    #[IA\Type('integer')]
    public ?int $ancestor = null;

    /**
     * @var int[]
     */
    #[IA\Expose]
    #[IA\Related('sessionObjectives')]
    #[IA\Type('array<string>')]
    public array $descendants = [];

    public function __construct(int $id, string $title, int $position, bool $active)
    {
        $this->id = $id;
        $this->title = $title;
        $this->position = $position;
        $this->active = $active;
    }
}
