<?php

namespace App\Tests\Endpoints;

/**
 * MeshQualifier API endpoint Test.
 * @group api_1
 */
class MeshQualifierTest extends AbstractMeshTest
{
    protected $testName =  'meshQualifiers';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadMeshQualifierData',
        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest()
    {
        return [
            'createdAt' => ['createdAt', 1, 99],
            'updatedAt' => ['updatedAt', 1, 99],
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest()
    {
        return [
            'id' => [[0], ['id' => '1']],
            'ids' => [[0, 1], ['id' => ['1', '2']]],
            'name' => [[1], ['name' => 'second qualifier']],
            'descriptors' => [[0, 1], ['descriptors' => ['abc1']]],
        ];
    }

    public function getTimeStampFields()
    {
        return ['createdAt', 'updatedAt'];
    }
}