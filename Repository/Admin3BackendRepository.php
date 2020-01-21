<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Repository\ExprFilterTrait;
use App\GptGeonamesBundle\Entity\Admin3;
use Doctrine\ORM\{AbstractQuery,
    EntityManagerInterface,
    EntityRepository,
    NonUniqueResultException,
    Query\Expr,
    QueryBuilder};

class Admin3BackendRepository
{
    use ExprFilterTrait;
    /**
     * @var EntityRepository
     */
    private $repository;



    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Admin3::class);
    }

    /**
     * @param Admin3 $admin3
     * @param string $alias
     * @param array $select
     * @return array|null
     */
    public function findByAdmin3(Admin3 $admin3, string $alias= 'findByAdmin3', array $select= []): ?array
    {
        $qb = $this->repository->createQueryBuilder($alias);
        $this->addExprFilter($qb, $admin3, $alias, [
                'like'=> ['name', 'nameascii'],
                'eq'=>['admin3id','geonameid', 'country', 'admin1', 'admin2']
            ]
        );
        if($qb->getDQLPart('where')==null) return [];
        return $qb->select(empty($select)? $alias : $select)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }


    /**
     * Admin3 pager
     * @param Admin3 $admin3
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, array(HYDRATE_OBJECT)]
     * @throws NonUniqueResultException
     */
    public function pageByAdmin3(Admin3 $admin3, $page, $ipp): array
    {
        $alias = 'admin3';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $this->addExprFilter($qb, $admin3, $alias, [
                'like'=> ['name', 'nameascii'],
                'eq'=>['admin3id','geonameid', 'country', 'admin1', 'admin2']
            ]
        );
        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.admin3id'))->getQuery()->getSingleScalarResult();
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
        $alias = 'admin3';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.admin3id',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }
        return $qb->getQuery()->getArrayResult();
    }
}
