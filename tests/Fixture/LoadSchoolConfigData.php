<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\SchoolConfig;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadSchoolConfigData extends AbstractFixture implements
    ORMFixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('App\Tests\DataLoader\SchoolConfigData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new SchoolConfig();
            $entity->setId($arr['id']);
            $entity->setName($arr['name']);
            $entity->setValue($arr['value']);
            $entity->setSchool($this->getReference('schools' . $arr['school']));
            $manager->persist($entity);
            $this->addReference('schoolConfigs' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            'App\Tests\Fixture\LoadSchoolData',
        ];
    }
}
