<?php

declare(strict_types=1);

namespace App\Tests;

/**
 * Trait PostEndpointTestable
 * @package App\Tests
 */
trait PostEndpointTestable
{
    /**
     * @see PostEndpointTestInterface::testPostOne()
     */
    public function testPostOne()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->create();
        $postData = $data;
        $this->postTest($data, $postData);
    }

    /**
     * @see PostEndpointTestInterface::testPostBad()
     */
    public function testPostBad()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->createInvalid();
        $this->badPostTest($data);
    }

    /**
     * @see PostEndpointTestInterface::testPostMany()
     */
    public function testPostMany()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->createMany(51);
        $this->postManyTest($data);
    }
}
