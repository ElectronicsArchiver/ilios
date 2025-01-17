<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\Fixture\LoadCurriculumInventoryAcademicLevelData;
use App\Tests\Fixture\LoadCurriculumInventoryExportData;
use App\Tests\Fixture\LoadCurriculumInventoryReportData;
use App\Tests\Fixture\LoadCurriculumInventorySequenceBlockData;
use App\Tests\Fixture\LoadProgramData;
use App\Tests\ReadEndpointTest;

/**
 * CurriculumInventoryAcademicLevel API endpoint Test.
 * @group api_4
 */
class CurriculumInventoryAcademicLevelTest extends ReadEndpointTest
{
    protected string $testName =  'curriculumInventoryAcademicLevels';

    protected function getFixtures(): array
    {
        return [
            LoadCurriculumInventoryAcademicLevelData::class,
            LoadCurriculumInventoryReportData::class,
            LoadCurriculumInventoryExportData::class,
            LoadCurriculumInventorySequenceBlockData::class,
            LoadProgramData::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest(): array
    {
        return [
            'id' => [[0], ['id' => 1]],
            'ids' => [[0, 1], ['id' => [1, 2]]],
            'name' => [[1], ['name' => 'second name']],
            'description' => [[0], ['description' => 'first description']],
            'level' => [[1], ['level' => 2]],
            'report' => [[0, 1, 2], ['report' => '1']],
            'startingSequenceBlocks' => [[1], ['startingSequenceBlocks' => 2]],
            'endingSequenceBlocks' => [[2], ['endingSequenceBlocks' => 2]],
        ];
    }
}
