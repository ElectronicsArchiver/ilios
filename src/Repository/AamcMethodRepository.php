<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AamcMethod;
use App\Traits\ImportableEntityRepository;
use App\Traits\ManagerRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use App\Entity\DTO\AamcMethodDTO;
use Doctrine\Persistence\ManagerRegistry;

use function array_values;

class AamcMethodRepository extends ServiceEntityRepository implements
    DTORepositoryInterface,
    RepositoryInterface,
    DataImportRepositoryInterface
{
    use ManagerRepository;
    use ImportableEntityRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AamcMethod::class);
    }

    /**
     * Find and hydrate as DTOs
     */
    public function findDTOsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->_em->createQueryBuilder()->select('x')->distinct()->from(AamcMethod::class, 'x');
        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);
        $dtos = [];
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $arr) {
            $dtos[$arr['id']] = new AamcMethodDTO(
                $arr['id'],
                $arr['description'],
                $arr['active']
            );
        }
        $dtos = $this->attachRelatedToDtos(
            $dtos,
            ['sessionTypes'],
        );

        return array_values($dtos);
    }

    protected function attachCriteriaToQueryBuilder(
        QueryBuilder $qb,
        array $criteria,
        ?array $orderBy,
        ?int $limit,
        ?int $offset
    ): void {
        if (array_key_exists('sessionTypes', $criteria)) {
            $ids = is_array($criteria['sessionTypes']) ? $criteria['sessionTypes'] : [$criteria['sessionTypes']];
            $qb->join('x.sessionTypes', 'st');
            $qb->andWhere($qb->expr()->in('st.id', ':sessionTypes'));
            $qb->setParameter(':sessionTypes', $ids);
        }

        //cleanup all the possible relationship filters
        unset($criteria['sessionTypes']);

        $this->attachClosingCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);
    }

    public function import(array $data, string $type, array $referenceMap): array
    {
        // `method_id`,`description`,`active`
        $entity = new AamcMethod();
        $entity->setId($data[0]);
        $entity->setDescription($data[1]);
        $entity->setActive((bool) $data[2]);
        $this->importEntity($entity);
        $referenceMap[$type . $entity->getId()] = $entity;
        return $referenceMap;
    }

    /**
     * Delete all records in this table
     */
    public function deleteAll(): void
    {
        $this->createQueryBuilder('a')->delete()->getQuery()->execute();
    }
}
