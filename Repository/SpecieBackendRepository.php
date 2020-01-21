<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Entity\Specie;
use Doctrine\ORM\{EntityManagerInterface, EntityRepository, NonUniqueResultException, Query\Expr};

class SpecieBackendRepository extends EntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Specie::class);
    }
    
    /**
     * Find by entity
     * @param Specie $specie
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageBySpecie(Specie $specie, $page, $ipp) : array
    {
        $alias = 'spcie';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $andx = $expr->andX();
        $params = [];

        //name LIKE value
        foreach(['name', 'commonname', 'genus', 'phylum', 'class', 'orden', 'family'] as $k)
        {
            if($specie->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = '%'.$specie->{'get'.  ucfirst($k)}().'%';
            }

        }

        //WHERE
        if($andx->count()>0){
            $qb->add('where', $andx)->setParameters($params);
        }

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$qb->expr()->count($alias.'.specieid'))->getQuery()->getSingleScalarResult();
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
        $alias = 'spcie';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();

        $selects = (new Expr\Select())->addMultiple(
            [
                $alias.'.specieid',
                $alias.'.name',
                $alias.'.commonname'
            ]
        );

        $qb->add('select', $selects, false)
            ->orderBy($alias.'.name','ASC');

        if(!empty($string)){
            $orx = $expr->orX();

            $orx->addMultiple([
                $expr->like($alias.'.name', ':name'),
                $expr->like($alias.'.commonname', ':name')
            ]);

            $qb->add('where', $orx)
                ->setParameter(':name' , '%'.$string.'%');

        }

        return $qb->getQuery()->getArrayResult();
    }
}
