<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\Fixture\LoadCourseData;
use App\Tests\Fixture\LoadCurriculumInventoryReportData;
use App\Tests\Fixture\LoadProgramData;
use App\Tests\Fixture\LoadProgramYearData;
use App\Tests\Fixture\LoadSchoolData;
use App\Tests\Fixture\LoadSessionData;
use App\Tests\Fixture\LoadTermData;
use App\Tests\ReadWriteEndpointTest;

/**
 * Program API endpoint Test.
 * @group api_1
 */
class ProgramTest extends ReadWriteEndpointTest
{
    protected string $testName =  'programs';

    protected function getFixtures(): array
    {
        return [
            LoadProgramData::class,
            LoadTermData::class,
            LoadSchoolData::class,
            LoadProgramYearData::class,
            LoadCourseData::class,
            LoadSessionData::class,
            LoadCurriculumInventoryReportData::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest(): array
    {
        return [
            'title' => ['title', $this->getFaker()->text()],
            'shortTitle' => ['shortTitle', $this->getFaker()->text(10)],
            'duration' => ['duration', $this->getFaker()->randomDigit()],
            'school' => ['school', 3],
            'programYears' => ['programYears', [1], $skipped = true],
            'curriculumInventoryReports' => ['curriculumInventoryReports', [1], $skipped = true],
            'directors' => ['directors', [1, 3]],
        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest(): array
    {
        return [
            'id' => ['id', 1, 99],
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest(): array
    {
        return [
            'id' => [[0], ['id' => 1]],
            'ids' => [[0, 2], ['id' => [1, 3]]],
            'title' => [[1], ['title' => 'second program']],
            'shortTitle' => [[0], ['shortTitle' => 'fp']],
            'duration' => [[1, 2], ['duration' => 4]],
            'school' => [[2], ['school' => 2]],
            'schools' => [[0, 1], ['schools' => 1]],
            'programYears' => [[0], ['programYears' => [1]], $skipped = true],
            'curriculumInventoryReports' => [[0], ['curriculumInventoryReports' => [1]], $skipped = true],
            'directors' => [[0], ['directors' => [1]], $skipped = true],
            'durationAndSchool' => [[1], ['school' => 1, 'duration' => 4]],
            'courses' => [[1], ['courses' => [4]]],
            'sessions' => [[0], ['sessions' => [3]]],
            'terms' => [[0], ['terms' => [1]]],
        ];
    }

    /**
     * Delete Program 2 explicitly as Program 1 is linked
     * to School 1.  Since sqlite doesn't cascade this doesn't work
     * @inheritdoc
     */
    public function testDelete()
    {
        $this->deleteTest(2);
    }

    public function testRejectUnprivilegedPostProgram()
    {
        $dataLoader = $this->getDataLoader();
        $program = $dataLoader->getOne();
        $userId = 3;

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_programs_post',
                ['version' => $this->apiVersion]
            ),
            json_encode(['programs' => [$program]])
        );
    }

    public function testRejectUnprivilegedPutProgram()
    {
        $dataLoader = $this->getDataLoader();
        $program = $dataLoader->getOne();
        $userId = 3;

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'PUT',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_programs_put',
                ['version' => $this->apiVersion, 'id' => $program['id']]
            ),
            json_encode(['program' => $program])
        );
    }

    public function testRejectUnprivilegedPutNewProgram()
    {
        $dataLoader = $this->getDataLoader();
        $program = $dataLoader->getOne();
        $userId = 3;

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'PUT',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_programs_put',
                ['version' => $this->apiVersion, 'id' => $program['id'] * 10000]
            ),
            json_encode(['program' => $program])
        );
    }

    public function testRejectUnprivilegedDeleteProgram()
    {
        $dataLoader = $this->getDataLoader();
        $program = $dataLoader->getOne();
        $userId = 3;

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'DELETE',
            $this->getUrl(
                $this->kernelBrowser,
                'app_api_programs_delete',
                ['version' => $this->apiVersion, 'id' => $program['id']]
            )
        );
    }
}
