<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Organisation;
use App\GptCaveBundle\Doctrine\Paginator;
use Doctrine\{ORM\AbstractQuery,
    ORM\EntityManagerInterface,
    ORM\EntityRepository,
    ORM\NonUniqueResultException,
    ORM\Query\Expr};

class OrganisationBackendRepository extends EntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Organisation::class);
    }

    /**
     * Counter
     * @return int
     * @throws NonUniqueResultException
     */
    public function countAll(): int
    {
        $qb = $this->repository->createQueryBuilder('org');
        return $qb->select($qb->expr()->count('org.organisationid'))
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * Find by entity
     * @param Organisation $organisation
     * @param int $page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByOrganisation(Organisation $organisation, $page, $ipp): array
    {
        $alias = 'org';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $andx = $expr->andX();
        $params = [];

        //name LIKE value
        foreach(['name', 'initials', 'code', 'organisationid'] as $k)
        {
            if($organisation->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = '%'.$organisation->{'get'.  ucfirst($k)}().'%';
            }

        }

        //name = value
        foreach(['country', 'type', 'coverage', 'grouping', 'admin1', 'admin2', 'admin3'] as $k)
        {
            if($organisation->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->eq($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = $organisation->{'get'.  ucfirst($k)}();
            }
        }

        //WHERE
        if($andx->count()>0){
            $qb->add('where', $andx)->setParameters($params);
        }

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$qb->expr()->count($alias.'.organisationid'))->getQuery()->getSingleScalarResult();


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
        $alias = 'org';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();

        $selects = (new Expr\Select())->addMultiple(
            [
                $alias.'.organisationid',
                $alias.'.name']
        );

        $qb->add('select', $selects, false)
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->add('where',   $expr->like($alias.'.name', ':name') )
                ->setParameter(':name' , '%'.$string.'%');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Count ID generators, exclude active ID generator organisation.
     * @param Organisation|null $exclude
     * @return int
     * @throws NonUniqueResultException
     */
    public function countIdGenerators(Organisation $exclude = null): int
    {
        $alias='idg';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select('count('.$alias.'.organisationid)')
            ->where($alias.'.idgenerator = :number')
            ->setParameter(':number', 1);
        if($exclude instanceof Organisation){
            $qb->andWhere($alias.'.organisationid != :excludeId')
                ->setParameter(':excludeId', $exclude->getOrganisationid());
        }
        return $qb->getQuery()
        ->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getIdGenerators(): array
    {
        return $this->repository->createQueryBuilder('o')
            ->select(['o.organisationid', 'o.name', 'o.code', 'IDENTITY(o.country)'])
            ->where('o.idgenerator = :number')
            ->setParameter(':number', 1)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}

