<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Area;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Repository\ExprFilterTrait;
use Doctrine\ORM\{AbstractQuery, EntityManagerInterface, EntityRepository, NonUniqueResultException, Query\Expr};

class AreaBackendRepository
{
    use ExprFilterTrait;

    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Area::class);
    }

    /**
     * @param Area $area
     * @param string $alias
     * @param array $select
     * @return array|null
     */
    public function findByArea(Area $area, string $alias= 'findByArea', array $select= []): ?array
    {
        $qb = $this->repository->createQueryBuilder($alias);
        return $this->addExprFilter($qb, $area, $alias, [
                'like'=> ['name'],
                'eq'=>['areaid', 'code', 'country', 'admin1']
            ]
        )->select(empty($select)? $alias : $select)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Find by Area
     * @param Area $area
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByArea(Area $area, $page, $ipp): array
    {
        $alias = 'area';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();

        $this->addExprFilter($qb, $area, $alias, [
                'like'=> ['name', 'code', 'areaid', 'designation'],
                'eq'=>['country','admin1', 'mapsheet']
            ]
        );
        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.areaid'))->getQuery()->getSingleScalarResult();
        $pager = new Paginator($page, $ipp, $totalRows);

        /**
         * Reset SELECT and get result
         */
        $limits = $pager->getLimits();

        $qb->resetDQLPart('select')
            ->add('select', $alias)
            ->setFirstResult($limits[0])//start
            ->setMaxResults($limits[1]);//end;

        return [$pager, $qb->getQuery()->getResult()];
    }

    /**
     * Find by string for suggest
     * @param string $string
     * @return array
     */
    public function filterByString($string): array
    {
        $alias = 'area';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.areaid',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }
        return $qb->getQuery()->getArrayResult();
    }
}
