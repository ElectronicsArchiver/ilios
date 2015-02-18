<?php

namespace Ilios\CoreBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ilios\CoreBundle\Entity\SessionDescriptionInterface;

/**
 * SessionDescription manager service.
 * Class SessionDescriptionManager
 * @package Ilios\CoreBundle\Manager
 */
class SessionDescriptionManager implements SessionDescriptionManagerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param EntityManager $em
     * @param string $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em         = $em;
        $this->class      = $class;
        $this->repository = $em->getRepository($class);
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     *
     * @return SessionDescriptionInterface
     */
    public function findSessionDescriptionBy(
        array $criteria,
        array $orderBy = null
    ) {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return SessionDescriptionInterface[]|Collection
     */
    public function findSessionDescriptionsBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param SessionDescriptionInterface $sessionDescription
     * @param bool $andFlush
     */
    public function updateSessionDescription(
        SessionDescriptionInterface $sessionDescription,
        $andFlush = true
    ) {
        $this->em->persist($sessionDescription);
        if ($andFlush) {
            $this->em->flush();
        }
    }

    /**
     * @param SessionDescriptionInterface $sessionDescription
     */
    public function deleteSessionDescription(
        SessionDescriptionInterface $sessionDescription
    ) {
        $this->em->remove($sessionDescription);
        $this->em->flush();
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return SessionDescriptionInterface
     */
    public function createSessionDescription()
    {
        $class = $this->getClass();
        return new $class();
    }
}