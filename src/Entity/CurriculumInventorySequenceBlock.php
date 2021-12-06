<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\SessionsEntity;
use App\Attribute as IA;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Traits\DescribableEntity;
use App\Traits\IdentifiableEntity;
use App\Traits\TitledEntity;
use App\Traits\StringableIdEntity;
use App\Repository\CurriculumInventorySequenceBlockRepository;

/**
 * Class CurriculumInventorySequenceBlock
 */
#[ORM\Table(name: 'curriculum_inventory_sequence_block')]
#[ORM\Entity(repositoryClass: CurriculumInventorySequenceBlockRepository::class)]
#[IA\Entity]
class CurriculumInventorySequenceBlock implements CurriculumInventorySequenceBlockInterface
{
    use IdentifiableEntity;
    use DescribableEntity;
    use TitledEntity;
    use StringableIdEntity;
    use SessionsEntity;

    /**
     * @var int
     * @Assert\Type(type="integer")
     */
    #[ORM\Column(name: 'sequence_block_id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[IA\Expose]
    #[IA\Type('integer')]
    #[IA\ReadOnly]
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 200
     * )
     */
    #[ORM\Column(type: 'string', length: 200)]
    #[IA\Expose]
    #[IA\Type('string')]
    protected $title;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @Assert\AtLeastOneOf({
     *     @Assert\Blank,
     *     @Assert\Length(min=1,max=65000)
     * })
     */
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[IA\Expose]
    #[IA\Type('string')]
    protected $description;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Type(type="integer")
     */
    #[ORM\Column(name: 'required', type: 'integer')]
    #[IA\Expose]
    #[IA\Type('integer')]
    protected $required;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 3,
     * )
     */
    #[ORM\Column(name: 'child_sequence_order', type: 'smallint')]
    #[IA\Expose]
    #[IA\Type('integer')]
    protected $childSequenceOrder;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    #[ORM\Column(name: 'order_in_sequence', type: 'integer')]
    #[IA\Expose]
    #[IA\Type('integer')]
    protected $orderInSequence;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    #[ORM\Column(name: 'minimum', type: 'integer')]
    #[IA\Expose]
    #[IA\Type('integer')]
    protected $minimum;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    #[ORM\Column(name: 'maximum', type: 'integer')]
    #[IA\Expose]
    #[IA\Type('integer')]
    protected $maximum;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     * this field is currently tinyint data type in the db but used like a boolean
     */
    #[ORM\Column(name: 'track', type: 'boolean')]
    #[IA\Expose]
    #[IA\Type('boolean')]
    protected $track;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'start_date', type: 'date', nullable: true)]
    #[IA\Expose]
    #[IA\Type('dateTime')]
    protected $startDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'end_date', type: 'date', nullable: true)]
    #[IA\Expose]
    #[IA\Type('dateTime')]
    protected $endDate;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    #[ORM\Column(name: 'duration', type: 'integer')]
    #[IA\Expose]
    #[IA\Type('integer')]
    protected $duration;

    /**
     * @var CurriculumInventoryAcademicLevelInterface
     */
    #[ORM\ManyToOne(targetEntity: 'CurriculumInventoryAcademicLevel', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinColumn(
        name: 'academic_level_id',
        referencedColumnName: 'academic_level_id',
        nullable: false,
        onDelete: 'cascade'
    )]
    #[IA\Expose]
    #[IA\Type('entity')]
    protected $academicLevel;

    /**
     * @var CourseInterface
     */
    #[ORM\ManyToOne(targetEntity: 'Course', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'course_id')]
    #[IA\Expose]
    #[IA\Type('entity')]
    protected $course;

    /**
     * @var CurriculumInventorySequenceBlockInterface
     */
    #[ORM\ManyToOne(targetEntity: 'CurriculumInventorySequenceBlock', inversedBy: 'children')]
    #[ORM\JoinColumn(
        name: 'parent_sequence_block_id',
        referencedColumnName: 'sequence_block_id',
        onDelete: 'cascade'
    )]
    #[IA\Expose]
    #[IA\Type('entity')]
    protected $parent;

    /**
     * @var ArrayCollection|CurriculumInventorySequenceBlockInterface[]
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: 'CurriculumInventorySequenceBlock')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    #[IA\Expose]
    #[IA\Type('entityCollection')]
    protected $children;

    /**
     * @var CurriculumInventoryReportInterface
     * @Assert\NotNull()
     */
    #[ORM\ManyToOne(targetEntity: 'CurriculumInventoryReport', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'report_id', onDelete: 'cascade')]
    #[IA\Expose]
    #[IA\Type('entity')]
    protected $report;

    /**
     * @var ArrayCollection|SessionInterface[]
     */
    #[ORM\ManyToMany(targetEntity: 'Session', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinTable('curriculum_inventory_sequence_block_x_session')]
    #[ORM\JoinColumn(name: 'sequence_block_id', referencedColumnName: 'sequence_block_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'session_id', referencedColumnName: 'session_id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    #[IA\Expose]
    #[IA\Type('entityCollection')]
    protected $sessions;

    /**
     * @var ArrayCollection|SessionInterface[]
     */
    #[ORM\ManyToMany(targetEntity: 'Session', inversedBy: 'excludedSequenceBlocks')]
    #[ORM\JoinTable('curriculum_inventory_sequence_block_x_excluded_session')]
    #[ORM\JoinColumn(name: 'sequence_block_id', referencedColumnName: 'sequence_block_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'session_id', referencedColumnName: 'session_id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    #[IA\Expose]
    #[IA\Type('entityCollection')]
    protected $excludedSessions;


    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->excludedSessions = new ArrayCollection();
        $this->required = self::OPTIONAL;
        $this->track = false;
    }

    /**
     * @param int $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return int
     */
    public function getRequired(): int
    {
        return $this->required;
    }

    /**
     * @param int $childSequenceOrder
     */
    public function setChildSequenceOrder($childSequenceOrder)
    {
        $this->childSequenceOrder = $childSequenceOrder;
    }

    /**
     * @return int $childSequenceOrder
     */
    public function getChildSequenceOrder(): int
    {
        return $this->childSequenceOrder;
    }

    /**
     * @param int $orderInSequence
     */
    public function setOrderInSequence($orderInSequence)
    {
        $this->orderInSequence = $orderInSequence;
    }

    /**
     * @return int
     */
    public function getOrderInSequence(): int
    {
        return $this->orderInSequence;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMinimum(): int
    {
        return $this->minimum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;
    }

    /**
     * @return int
     */
    public function getMaximum(): int
    {
        return $this->maximum;
    }

    /**
     * @param bool $track
     */
    public function setTrack($track)
    {
        $this->track = $track;
    }

    /**
     * @return bool
     */
    public function hasTrack(): bool
    {
        return $this->track;
    }

    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setAcademicLevel(CurriculumInventoryAcademicLevelInterface $academicLevel)
    {
        $this->academicLevel = $academicLevel;
    }

    /**
     * @return CurriculumInventoryAcademicLevelInterface
     */
    public function getAcademicLevel(): CurriculumInventoryAcademicLevelInterface
    {
        return $this->academicLevel;
    }

    public function setCourse(CourseInterface $course = null)
    {
        $this->course = $course;
    }

    public function getCourse(): ?CourseInterface
    {
        return $this->course;
    }

    public function setParent(CurriculumInventorySequenceBlockInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return CurriculumInventorySequenceBlockInterface
     */
    public function getParent(): CurriculumInventorySequenceBlockInterface
    {
        return $this->parent;
    }

    public function setChildren(Collection $children)
    {
        $this->children = new ArrayCollection();

        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    public function addChild(CurriculumInventorySequenceBlockInterface $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }
    }

    public function removeChild(CurriculumInventorySequenceBlockInterface $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return ArrayCollection|CurriculumInventorySequenceBlockInterface[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setReport(CurriculumInventoryReportInterface $report)
    {
        $this->report = $report;
    }

    /**
     * @return CurriculumInventoryReportInterface
     */
    public function getReport(): CurriculumInventoryReportInterface
    {
        return $this->report;
    }

    public function getChildrenAsSortedList(): array
    {
        $children = $this->getChildren()->toArray();
        $sortStrategy = $this->getChildSequenceOrder();
        switch ($sortStrategy) {
            case self::ORDERED:
                usort($children, [__CLASS__, 'compareSequenceBlocksWithOrderedStrategy']);
                break;
            case self::UNORDERED:
            case self::PARALLEL:
            default:
                usort($children, [__CLASS__, 'compareSequenceBlocksWithDefaultStrategy']);
                break;
        }
        return $children;
    }

    /**
     * Callback function for comparing sequence blocks.
     * The applied criterion for comparison is the </pre>"orderInSequence</pre> property.
     *
     * @return int One of -1, 0, 1.
     */
    public static function compareSequenceBlocksWithOrderedStrategy(
        CurriculumInventorySequenceBlockInterface $a,
        CurriculumInventorySequenceBlockInterface $b
    ) {
        if ($a->getOrderInSequence() === $b->getOrderInSequence()) {
            return 0;
        }
        return ($a->getOrderInSequence() > $b->getOrderInSequence()) ? 1 : -1;
    }

    /**
     * Callback function for comparing sequence blocks.
     * The applied, ranked criteria for comparison are:
     * 1. "academic level"
     *      Numeric sort, ascending.
     * 2. "start date"
     *      Numeric sort on timestamps, ascending. NULL values will be treated as unix timestamp 0.
     * 3. "title"
     *    Alphabetical sort.
     * 4. "sequence block id"
     *    A last resort. Numeric sort, ascending.
     *
     * @return int One of -1, 0, 1.
     */
    public static function compareSequenceBlocksWithDefaultStrategy(
        CurriculumInventorySequenceBlockInterface $a,
        CurriculumInventorySequenceBlockInterface $b
    ) {
        // 1. academic level id
        if ($a->getAcademicLevel()->getLevel() > $b->getAcademicLevel()->getLevel()) {
            return 1;
        } elseif ($a->getAcademicLevel()->getLevel() < $b->getAcademicLevel()->getLevel()) {
            return -1;
        }

        // 2. start date
        $startDateA = $a->getStartDate() ? $a->getStartDate()->getTimestamp() : 0;
        $startDateB = $b->getStartDate() ? $b->getStartDate()->getTimestamp() : 0;

        if ($startDateA > $startDateB) {
            return 1;
        } elseif ($startDateA < $startDateB) {
            return -1;
        }

        // 3. title comparison
        $n = strcasecmp($a->getTitle(), $b->getTitle());
        if ($n) {
            return $n > 0 ? 1 : -1;
        }

        // 4. sequence block id comparison
        if ($a->getId() > $b->getId()) {
            return 1;
        } elseif ($a->getId() < $b->getId()) {
            return -1;
        }
        return 0;
    }

    public function setExcludedSessions(Collection $sessions)
    {
        $this->excludedSessions = new ArrayCollection();

        foreach ($sessions as $session) {
            $this->addExcludedSession($session);
        }
    }

    public function addExcludedSession(SessionInterface $session)
    {
        if (!$this->excludedSessions->contains($session)) {
            $this->excludedSessions->add($session);
        }
    }

    public function removeExcludedSession(SessionInterface $session)
    {
        $this->excludedSessions->removeElement($session);
    }

    public function getExcludedSessions(): Collection
    {
        return $this->excludedSessions;
    }
}
