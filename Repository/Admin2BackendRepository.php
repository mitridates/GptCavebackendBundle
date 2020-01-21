<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Repository\ExprFilterTrait;
use App\GptGeonamesBundle\Entity\Admin2;
use Doctrine\ORM\{AbstractQuery,
    EntityManagerInterface,
    EntityRepository,
    NonUniqueResultException,
    Query\Expr,
    QueryBuilder};

class Admin2BackendRepository
{
    use ExprFilterTrait;

    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Admin2::class);
    }

    /**
     * @param Admin2 $admin2
     * @param string $alias
     * @param array $select
     * @return array|null
     */
    public function findByAdmin2(Admin2 $admin2, string $alias= 'findByAdmin2', array $select= []): ?array
    {
        $qb = $this->repository->createQueryBuilder($alias);
        return $this->addExprFilter($qb, $admin2, $alias, [
                'like'=> ['name', 'nameascii'],
                'eq'=>['admin2id','geonameid', 'country', 'admin1']
            ]
        )->select(empty($select)? $alias : $select)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }


    /**
     * Admin2 pager
     * @param Admin2 $admin2
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, array]
     * @throws NonUniqueResultException
     */
    public function pageByAdmin2(Admin2 $admin2, $page, $ipp): array
    {
        $alias = 'admin2';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $this->addExprFilter($qb, $admin2, $alias, [
                'like'=> ['name', 'nameascii'],
                'eq'=>['admin2id','geonameid', 'country', 'admin1']
            ]
        );
        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.admin2id'))->getQuery()->getSingleScalarResult();
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
        $alias = 'admin2';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.admin2id',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }
        return $qb->getQuery()->getArrayResult();
    }
}
