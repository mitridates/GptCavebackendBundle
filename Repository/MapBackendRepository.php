<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Map;
use App\GptCaveBundle\Doctrine\Paginator;
use Doctrine\ORM\{EntityManagerInterface, EntityRepository, Mapping\OneToMany, NonUniqueResultException, Query\Expr};

class MapBackendRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Map::class);
    }
    
    /**
     * Find by entity
     *
     * @param Map $map
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByMap(Map $map, $page, $ipp)
    {
        $alias = 'map';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $andx = $expr->andX();
        $params = [];

        //name LIKE value
        foreach(['mapid', 'name'] as $k)
        {
            if($map->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = '%'.$map->{'get'.  ucfirst($k)}().'%';
            }

        }

        //name = value
        foreach(['sourcecountry','scopecountry','scopeadmin1','scopeadmin2','scopeadmin3','sourceorg','type','sourcetype','mapserie'] as $k)
        {
            if($map->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->eq($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = $map->{'get'.  ucfirst($k)}();
            }
        }

        //WHERE
        if($andx->count()>0){
            $qb->add('where', $andx)->setParameters($params);
        }

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.mapid'))->getQuery()->getSingleScalarResult();


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
     * @param $class OneToMany Mapxxx Entity
     * @param $mapid
     * @param int $page
     * @param int $ipp
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageOnetomanyByMapId($class, $mapid, $page, $ipp)
    {
        $alias = 'momap';
        $expr = new Expr();
        $qb =  $this->getEntityManager()->createQueryBuilder()
            ->select($alias)
            ->from($class, $alias)
            ->where($alias.'.map = :mapid')
            ->orderBy($alias.'.position', 'ASC')
            ->addOrderBy($alias.'.sequence', 'ASC')
            ->setParameter(':mapid', $mapid);

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.map'))->getQuery()->getSingleScalarResult();


        $pager = new Paginator($page, $ipp, $totalRows);

        $limits = $pager->getLimits();

        $qb->resetDQLPart('select')
            ->add('select', $alias)
            ->setFirstResult($limits[0])//start
            ->setMaxResults($limits[1]);//end;

        return [$pager, $qb->getQuery()->getResult()];

    }

    /**
     * Find by string for suggest
     *
     * @param string $string
     * @return array
     */
    public function filterByString($string)
    {
        $alias = 'map';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.mapid',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }
        return $qb->getQuery()->getArrayResult();
    }

}
