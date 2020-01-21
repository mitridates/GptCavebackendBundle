<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Cave;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Repository\ExprFilterTrait;
use Doctrine\ORM\{EntityManagerInterface, EntityRepository, NonUniqueResultException, Query\Expr};

class CaveBackendRepository
{
    use ExprFilterTrait;

    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Cave::class);
    }
    
    /**
     * Find by Cave
     * @param Cave $cave
     * @param int $page     current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByCave(Cave $cave, $page, $ipp) : array
    {
        $alias = 'cavePagination';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();

        $this->addExprFilter($qb, $cave, $alias, [
                'like'=> ['caveid', 'name', 'serial', 'nearestlocality', 'localgovernmentarea'],
                'eq'=>['country','admin1','admin2','admin3']
            ]
        );

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.caveid'))->getQuery()->getSingleScalarResult();
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
    public function filterByString($string)
    {
        $alias = 'caveString';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.caveid',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Page Onetomany entity
     * @param string $classpath Manytoone entity classpath
     * @param string $caveid
     * @param int $page
     * @param int $ipp
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageOnetomanyByCaveId($classpath, $caveid, $page, $ipp)
    {
        $alias = 'caveOnetomany';
        $qb = $this->repository->createQueryBuilder()
            ->select($alias)
            ->where((new Expr())->eq($alias.'.cave', ':caveid' ))
            ->setParameter(':caveid', $caveid);

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$qb->expr()->count($alias.'.cave'))->getQuery()->getSingleScalarResult();


        $pager = new Paginator($page, $ipp, $totalRows);

        /**
         * Reset SELECT and get result
         */
        $limits = $pager->getLimits();

        $qb->resetDQLPart('select')
            ->add('select', $alias)
            ->orderBy($alias.'.position', 'ASC')
            ->addOrderBy($alias.'.sequence', 'ASC')
            ->setFirstResult($limits[0])//start
            ->setMaxResults($limits[1]);//end;

        return [$pager, $qb->getQuery()->getResult()];
    }
}

