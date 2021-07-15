<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Alert;
use App\Traits\ManagerRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use App\Entity\DTO\AlertDTO;
use Doctrine\Persistence\ManagerRegistry;

class AlertRepository extends ServiceEntityRepository implements DTORepositoryInterface, RepositoryInterface
{
    use ManagerRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alert::class);
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT x')->from('App\Entity\Alert', 'x');

        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find and hydrate as DTOs
     *
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     *
     */
    public function findDTOsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->_em->createQueryBuilder()->select('x')->distinct()->from('App\Entity\Alert', 'x');
        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);
        $alertDTOs = [];
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $arr) {
            $alertDTOs[$arr['id']] = new AlertDTO(
                $arr['id'],
                $arr['tableRowId'],
                $arr['tableName'],
                $arr['additionalText'],
                $arr['dispatched']
            );
        }
        $alertIds = array_keys($alertDTOs);
        $related = [
            'changeTypes',
            'instigators',
            'recipients'
        ];
        foreach ($related as $rel) {
            $qb = $this->_em->createQueryBuilder()
                ->select('r.id AS relId, x.id AS alertId')->from('App\Entity\Alert', 'x')
                ->join("x.{$rel}", 'r')
                ->where($qb->expr()->in('x.id', ':alertIds'))
                ->orderBy('relId')
                ->setParameter('alertIds', $alertIds);
            foreach ($qb->getQuery()->getResult() as $arr) {
                $alertDTOs[$arr['alertId']]->{$rel}[] = $arr['relId'];
            }
        }
        return array_values($alertDTOs);
    }


    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return QueryBuilder
     */
    protected function attachCriteriaToQueryBuilder(QueryBuilder $qb, $criteria, $orderBy, $limit, $offset)
    {
        if (array_key_exists('changeTypes', $criteria)) {
            $ids = is_array($criteria['changeTypes']) ?
                $criteria['changeTypes'] : [$criteria['changeTypes']];
            $qb->join('x.changeTypes', 'act');
            $qb->andWhere($qb->expr()->in('act.id', ':changeTypes'));
            $qb->setParameter(':changeTypes', $ids);
        }
        if (array_key_exists('instigators', $criteria)) {
            $ids = is_array($criteria['instigators']) ?
                $criteria['instigators'] : [$criteria['instigators']];
            $qb->join('x.instigators', 'ins');
            $qb->andWhere($qb->expr()->in('ins.id', ':instigators'));
            $qb->setParameter(':instigators', $ids);
        }
        if (array_key_exists('recipients', $criteria)) {
            $ids = is_array($criteria['recipients']) ?
                $criteria['recipients'] : [$criteria['recipients']];
            $qb->join('x.recipients', 'rcp');
            $qb->andWhere($qb->expr()->in('rcp.id', ':recipients'));
            $qb->setParameter(':recipients', $ids);
        }

        //cleanup all the possible relationship filters
        unset($criteria['changeTypes']);
        unset($criteria['instigators']);
        unset($criteria['recipients']);

        if ($criteria !== []) {
            foreach ($criteria as $key => $value) {
                $values = is_array($value) ? $value : [$value];
                $qb->andWhere($qb->expr()->in("x.{$key}", ":{$key}"));
                $qb->setParameter(":{$key}", $values);
            }
        }

        if (empty($orderBy)) {
            $orderBy = ['id' => 'ASC'];
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $qb->addOrderBy('x.' . $sort, $order);
            }
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
}
