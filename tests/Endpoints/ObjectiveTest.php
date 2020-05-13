<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\DataLoader\CourseData;
use App\Tests\DataLoader\CourseObjectiveData;
use App\Tests\DataLoader\ProgramYearData;
use App\Tests\DataLoader\ProgramYearObjectiveData;
use App\Tests\DataLoader\SessionData;
use App\Tests\DataLoader\SessionObjectiveData;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\DataLoader\ObjectiveData;
use App\Tests\ReadWriteEndpointTest;

/**
 * Objective API endpoint Test.
 * @group api_5
 */
class ObjectiveTest extends ReadWriteEndpointTest
{
    protected $testName =  'objectives';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadObjectiveData',
            'App\Tests\Fixture\LoadCompetencyData',
            'App\Tests\Fixture\LoadCourseData',
            'App\Tests\Fixture\LoadProgramYearData',
            'App\Tests\Fixture\LoadSessionData',
            'App\Tests\Fixture\LoadMeshDescriptorData',
            'App\Tests\Fixture\LoadSessionObjectiveData',
            'App\Tests\Fixture\LoadCourseObjectiveData',
            'App\Tests\Fixture\LoadProgramYearObjectiveData',
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest()
    {
        return [
            'title' => ['title', $this->getFaker()->text],
            'position' => ['position', $this->getFaker()->randomDigit],
            'notActive' => ['active', false],
            'competency' => ['competency', 1],
            'parents' => ['parents', [2]],
            'children' => ['children', [4]],
            'meshDescriptors' => ['meshDescriptors', ['abc2']],
            'ancestor' => ['ancestor', 1, $skipped = true],
            'descendants' => ['descendants', [2], $skipped = true],
        ];
    }

    public function testPutXObjectives()
    {
        $dataLoader = $this->getDataLoader();
        $objective = $dataLoader->create();
        unset($objective['id']);
        $objective = $this->postOne('objectives', 'objective', 'objectives', $objective);

        $dataLoader = $this->getContainer()->get(CourseData::class);
        $course = $dataLoader->getOne();
        $dataLoader = $this->getContainer()->get(CourseObjectiveData::class);
        $courseObjective = $dataLoader->create();
        $courseObjective['course'] = $course['id'];
        $courseObjective['objective'] = $objective['id'];
        unset($courseObjective['id']);
        $courseObjective = $this->postOne('courseobjectives', 'courseObjective', 'courseObjectives', $courseObjective);

        $dataLoader = $this->getContainer()->get(ProgramYearData::class);
        $programYear = $dataLoader->getOne();
        $dataLoader = $this->getContainer()->get(ProgramYearObjectiveData::class);
        $programYearObjective = $dataLoader->create();
        $programYearObjective['programYear'] = $programYear['id'];
        $programYearObjective['objective'] = $objective['id'];
        unset($programYearObjective['id']);
        $programYearObjective = $this->postOne(
            'programyearobjectives',
            'programYearObjective',
            'programYearObjectives',
            $programYearObjective
        );

        $dataLoader = $this->getContainer()->get(SessionData::class);
        $session = $dataLoader->getOne();
        $dataLoader = $this->getContainer()->get(SessionObjectiveData::class);
        $sessionObjective = $dataLoader->create();
        $sessionObjective['session'] = $session['id'];
        $sessionObjective['objective'] = $objective['id'];
        unset($sessionObjective['id']);
        $sessionObjective = $this->postOne(
            'sessionobjectives',
            'sessionObjective',
            'sessionObjectives',
            $sessionObjective
        );

        $objective['courseObjectives'] = [ $courseObjective['id'] ];
        $objective['sessionObjectives'] = [ $sessionObjective['id'] ];
        $objective['programYearObjectives'] = [ $programYearObjective['id'] ];

        $this->putTest($objective, $objective, $objective['id']);
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest()
    {
        return [
            'id' => ['id', 1, 99],
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest()
    {
        return [
            'id' => [[0], ['id' => 1]],
            'title' => [[1], ['title' => 'second objective']],
            'position' => [[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10], ['position' => 0]],
            'active' => [[0, 1, 2, 3, 5, 7, 8, 9, 10], ['active' => 1]],
            'inactive' => [[4, 6], ['active' => 0]],
            'competency' => [[0], ['competency' => 3]],
            'courses' => [[1, 3], ['courses' => [2]]],
            'programYears' => [[0, 1], ['programYears' => [1]]],
            'sessions' => [[2], ['sessions' => [1]]],
//            'parents' => [[2, 5], ['parents' => [2]]],
//            'children' => [[1], ['children' => [3]]],
//            'meshDescriptors' => [[6], ['meshDescriptors' => ['abc3']]],
            'ancestor' => [[6], ['ancestor' => 6]],
//            'descendants' => [[1], ['descendants' => [3]]],
            'fullCoursesThroughCourse' => [[1, 3], ['fullCourses' => [2]]],
            'fullCoursesThroughSession' => [[1, 2], ['fullCourses' => [1]]],
        ];
    }

    /**
     * Ideally, we'd be testing the "purified textarea" form type by itself.
     * However, the framework currently does not provide boilerplate to roll container-aware form test.
     * We'd need a hybrid between <code>KernelTestCase</code> and <code>TypeTestCase</code>.
     * @link  http://symfony.com/doc/current/cookbook/testing/doctrine.html
     * @link http://symfony.com/doc/current/cookbook/form/unit_testing.html
     * To keep things easy, I bolted this test on to this controller test for the time being.
     * @todo Revisit occasionally and check if future versions of Symfony have addressed this need. [ST 2015/10/19]
     *
     * @dataProvider inputSanitationTestProvider
     *
     * @param string $input A given objective title as un-sanitized input.
     * @param string $output The expected sanitized objective title output as returned from the server.
     *
     */
    public function testInputSanitation($input, $output)
    {
        $postData = $this->getContainer()->get(ObjectiveData::class)
            ->create();
        $postData['title'] = $input;
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl($this->kernelBrowser, 'ilios_api_post', [
                'version' => $this->apiVersion,
                'object' => 'objectives'
            ]),
            json_encode(['objectives' => [$postData]]),
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertEquals(
            json_decode($response->getContent(), true)['objectives'][0]['title'],
            $output,
            $response->getContent()
        );
    }

    /**
     * @return array
     */
    public function inputSanitationTestProvider()
    {
        return [
            ['foo', 'foo'],
            ['<p>foo</p>', '<p>foo</p>'],
            ['<ul><li>foo</li></ul>', '<ul><li>foo</li></ul>'],
            ['<script>alert("hello");</script><p>foo</p>', '<p>foo</p>'],
            [
                '<a href="https://iliosproject.org" target="_blank">Ilios</a>',
                '<a href="https://iliosproject.org" target="_blank" rel="noreferrer noopener">Ilios</a>'
            ],
        ];
    }

    /**
     * Assert that a POST request fails if form validation fails due to input sanitation.
     */
    public function testInputSanitationFailure()
    {
        $postData = $this->getContainer()->get(ObjectiveData::class)
            ->create();
        // this markup will get stripped out, leaving a blank string as input.
        // which in turn will cause the form validation to fail.
        $postData['title'] = '<iframe></iframe>';
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl($this->kernelBrowser, 'ilios_api_post', [
                'version' => $this->apiVersion,
                'object' => 'objectives'
            ]),
            json_encode(['objectives' => [$postData]]),
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);
    }
}
