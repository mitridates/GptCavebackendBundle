<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Repository\ExprFilterTrait;
use App\GptGeonamesBundle\Entity\Admin1;
use Doctrine\ORM\{AbstractQuery,
    EntityManagerInterface,
    EntityRepository,
    NonUniqueResultException,
    Query\Expr,
    QueryBuilder};

class Admin1BackendRepository
{
    use ExprFilterTrait;

    /**
     * @var EntityRepository
     */
    private $repository;



    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Admin1::class);
    }

    /**
     * @param Admin1 $admin1
     * @param string $alias
     * @param array $select
     * @return array|null
     */
    public function findByAdmin1(Admin1 $admin1, string $alias= 'findByAdmin1', array $select= []): ?array
    {
        $qb = $this->repository->createQueryBuilder($alias);
        return $this->addExprFilter($qb, $admin1, $alias, [
                'like'=> ['name', 'nameascii'],
                'eq'=>['admin1id','geonameid', 'country']
            ]
        )->select(empty($select)? $alias : $select)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }


    /**
     * Admin1 pager
     * @param Admin1 $admin1
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, array]
     * @throws NonUniqueResultException
     */
    public function pageByAdmin1(Admin1 $admin1, $page, $ipp): array
    {
        $alias = 'admin1';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $this->addExprFilter($qb, $admin1, $alias, [
                'like'=> ['name', 'nameascii'],
                'eq'=>['admin1id','geonameid', 'country']
            ]
        );

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.admin1id'))->getQuery()->getSingleScalarResult();
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
        $alias = 'admin1';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.admin1id',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }
        return $qb->getQuery()->getArrayResult();
    }
}
