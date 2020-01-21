<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Mapserie;
use App\GptCaveBundle\Doctrine\Paginator;
use Doctrine\{ORM\EntityManagerInterface, ORM\EntityRepository, ORM\NonUniqueResultException, ORM\Query\Expr};

class MapserieBackendRepository extends EntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Mapserie::class);
    }
    
    /**
     * Find by entity
     * @param Mapserie $mapserie Búsqueda del formulario MapserieFormType o entidad vacía
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByMapserie(Mapserie $mapserie, $page, $ipp) : array
    {
        $alias = 'mapser';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $andx = $expr->andX();
        $params = [];

        //name LIKE value
        foreach(['name', 'code', 'scale', 'abbreviation'] as $k)
        {
            if($mapserie->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = '%'.$mapserie->{'get'.  ucfirst($k)}().'%';
            }

        }

        //name = value
        foreach(['publisher'] as $k)
        {
            if($mapserie->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->eq($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = $mapserie->{'get'.  ucfirst($k)}();
            }
        }

        //WHERE
        if($andx->count()>0){
            $qb->add('where', $andx)->setParameters($params);
        }

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$qb->expr()->count($alias.'.mapserieid'))->getQuery()->getSingleScalarResult();


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
        $alias = 'mapser';
        $qb = $this->repository->createQueryBuilder($alias);
        $selects = (new Expr\Select())->addMultiple(
            [
                $alias.'.mapserieid',
                $alias.'.name'
            ]
        );

        $qb->add('select', $selects)
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->add('where', $qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }

        return $qb->getQuery()->getArrayResult();
    }
}
