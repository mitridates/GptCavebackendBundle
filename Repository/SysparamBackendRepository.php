<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\{Organisation, Sysparam};
use App\GptCaveBundle\Doctrine\Paginator;
use Doctrine\{ORM\AbstractQuery,
    ORM\EntityManagerInterface,
    ORM\EntityRepository,
    ORM\NonUniqueResultException,
    ORM\Query\Expr};


class SysparamBackendRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Sysparam::class);
    }

    /**
     * @return Sysparam|null
     * @throws NonUniqueResultException
     */
    public function getSystemParameters(): ?Sysparam
    {
        return $this->repository->createQueryBuilder('sysp')
            ->select('sysp')
            ->orderBy('sysp.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $select
     * @return array|null
     * @throws NonUniqueResultException
     */
    public function getSystemParametersValues(array $select): ?array
    {
        $qb= $this->repository->createQueryBuilder('sysp')
        ->select('sysp.id');
        foreach ($select as $item) {
            if(in_array($item, ['organisationdbm', 'organisationsite', 'country', 'mapserie', 'refunits', 'altitudeunit'])){
                $qb->addSelect('IDENTITY(sysp.'.$item.') AS '. $item);
            }else{
                $qb->addSelect('sysp.'.$item);
            }
        }
        return $qb->orderBy('sysp.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param string $select
     * @return string|null
     * @throws NonUniqueResultException
     */
    public function getSystemParametersValue(string $select): ?string
    {
        return $this->getSystemParametersValues([$select])[$select];
    }
}
