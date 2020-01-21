<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Doctrine\Paginator;
use App\GptCaveBundle\Entity\Person;
use Doctrine\ORM\{EntityManagerInterface, EntityRepository, NonUniqueResultException, Query\Expr};
class PersonBackendRepository extends EntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Person::class);
    }
    
    /**
     * Find by entity
     * @param Person $person
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByPerson(Person $person, $page, $ipp) : array
    {
        $alias = 'prsn';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $andx = $expr->andX();
        $params = [];

       //name LIKE value
        foreach(['name', 'surname', 'personid'] as $k)
        {
            if($person->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = '%'.$person->{'get'.  ucfirst($k)}().'%';
            }

        }

        //name = value
        foreach(['country','admin2', 'admin3'] as $k)
        {
            if($person->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->eq($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = $person->{'get'.  ucfirst($k)}();
            }
        }

        //WHERE
        if($andx->count()>0){
            $qb->add('where', $andx)->setParameters($params);
        }

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$qb->expr()->count($alias.'.personid'))->getQuery()->getSingleScalarResult();


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
        $alias = 'prsn';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();

        $selects = (new Expr\Select())->addMultiple(
            [
                $alias.'.personid',
                $alias.'.name',
                $alias.'.surname'
            ]
        );

        $qb->add('select', $selects, false)
            ->orderBy($alias.'.name','ASC');

        if(!empty($string)){
            $orx = $expr->orX();

            $orx->addMultiple([
                $expr->like($alias.'.name', ':name'),
                $expr->like($alias.'.surname', ':name')
            ]);

            $qb->add('where', $orx)
                ->setParameter(':name' , '%'.$string.'%');

        }

        return $qb->getQuery()->getArrayResult();
    }
}
