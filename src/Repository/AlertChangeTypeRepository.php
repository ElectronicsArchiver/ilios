<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AlertChangeType;
use App\Traits\ImportableEntityRepository;
use App\Traits\ManagerRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use App\Entity\DTO\AlertChangeTypeDTO;
use Doctrine\Persistence\ManagerRegistry;

use function array_values;

class AlertChangeTypeRepository extends ServiceEntityRepository implements
    DTORepositoryInterface,
    RepositoryInterface,
    DataImportRepositoryInterface
{
    use ManagerRepository;
    use ImportableEntityRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlertChangeType::class);
    }

    /**
     * Find and hydrate as DTOs
     */
    public function findDTOsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->_em->createQueryBuilder()->select('x')->distinct()->from(AlertChangeType::class, 'x');
        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);
        $dtos = [];
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $arr) {
            $dtos[$arr['id']] = new AlertChangeTypeDTO(
                $arr['id'],
                $arr['title']
            );
        }
        $dtos = $this->attachRelatedToDtos(
            $dtos,
            ['alerts'],
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
        if (array_key_exists('alerts', $criteria)) {
            $ids = is_array($criteria['alerts']) ? $criteria['alerts'] : [$criteria['alerts']];
            $qb->join('x.alerts', 'al');
            $qb->andWhere($qb->expr()->in('al.id', ':alerts'));
            $qb->setParameter(':alerts', $ids);
        }

        //cleanup all the possible relationship filters
        unset($criteria['alerts']);

        $this->attachClosingCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);
    }

    public function import(array $data, string $type, array $referenceMap): array
    {
        // `alert_change_type_id`,`title`
        $entity = new AlertChangeType();
        $entity->setId($data[0]);
        $entity->setTitle($data[1]);
        $this->importEntity($entity);
        $referenceMap[$type . $entity->getId()] = $entity;
        return $referenceMap;
    }
}
