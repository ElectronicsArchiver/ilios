<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\CurriculumInventoryInstitution;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCurriculumInventoryInstitutionData extends AbstractFixture implements
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
            ->get('App\Tests\DataLoader\CurriculumInventoryInstitutionData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new CurriculumInventoryInstitution();
            if (!empty($arr['school'])) {
                $entity->setSchool($this->getReference('schools' . $arr['school']));
            }
            $entity->setId($arr['id']);
            $entity->setName($arr['name']);
            $entity->setAamcCode($arr['aamcCode']);
            $entity->setAddressStreet($arr['addressStreet']);
            $entity->setAddressCity($arr['addressCity']);
            $entity->setAddressStateOrProvince($arr['addressStateOrProvince']);
            $entity->setAddressZipCode($arr['addressZipCode']);
            $entity->setAddressCountryCode($arr['addressCountryCode']);

            $manager->persist($entity);
            $this->addReference('curriculumInventoryInstitutions' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            'App\Tests\Fixture\LoadSchoolData'
        ];
    }
}
