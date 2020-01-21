<?php

namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Fielddefinition;
use App\GptCaveBundle\Entity\Fielddefinitionlang;
use App\GptCaveBundle\Entity\Fieldvaluecode;
use App\GptCaveBundle\Entity\Organisation;
use App\GptGeonamesBundle\Entity\Admin1;
use App\GptGeonamesBundle\Entity\Admin2;
use App\GptGeonamesBundle\Entity\Admin3;
use App\GptGeonamesBundle\Entity\Country;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class SetupBackendRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        $admin1Dql = $this->entityManager->getRepository(Admin1::class)
            ->createQueryBuilder('a1')
            ->select('count(a1)')
            ->where('a1.country= country.countryid')
            ->getDQL()
        ;
        $admin2Dql = $this->entityManager->getRepository(Admin2::class)
            ->createQueryBuilder('a2')
            ->select('count(a2)')
            ->where('a2.country= country.countryid')
            ->getDQL()
        ;
        $admin3Dql = $this->entityManager->getRepository(Admin3::class)
            ->createQueryBuilder('a3')
            ->select('count(a3)')
            ->where('a3.country= country.countryid')
            ->getDQL()
        ;

       return  $this->entityManager->getRepository(Country::class)->
        createQueryBuilder('country')
            ->select('country.countryid', 'country.name')
           ->addSelect('('.$admin1Dql.') AS admin1')
           ->addSelect('('.$admin2Dql.') AS admin2')
           ->addSelect('('.$admin3Dql.') AS admin3')
           ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param Country $country
     * @return void
     */
    public function deleteCountry(Country $country): void
    {
        $this->entityManager->getRepository(Admin3::class)
            ->createQueryBuilder('a3')
            ->delete(Admin3::class, 'a3')->where('a3.country = :country')
            ->setParameter('country', $country)
            ->getQuery()->execute();

        $this->entityManager->getRepository(Admin2::class)
            ->createQueryBuilder('a2')
            ->delete(Admin2::class, 'a2')->where('a2.country = :country')
            ->setParameter('country', $country)
            ->getQuery()->execute();

        $this->entityManager->getRepository(Admin1::class)
            ->createQueryBuilder('a1')
            ->delete(Admin1::class, 'a2')->where('a2.country = :country')
            ->setParameter('country', $country)
            ->getQuery()->execute();

        $this->entityManager->remove($country);
        $this->entityManager->flush();
        return;
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function countCountry(): int
    {
        return  $this->entityManager->getRepository(Country::class)->
        createQueryBuilder('Country')
            ->select('count(Country.countryid)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function countOrganisation(): int
    {
        return  $this->entityManager->getRepository(Organisation::class)->
        createQueryBuilder('organisation')
            ->select('count(organisation.organisationid)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Organisation
     * @throws NonUniqueResultException
     */
    public function getOrganisation(): Organisation
    {
        if($this->countOrganisation() != 1) return new Organisation();

        $organisation = $this->entityManager->createQueryBuilder()
            ->select('org')
            ->from(Organisation::class, 'org')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $organisation ?? new Organisation();
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function countFielddefinition(): int
    {
        return  $this->entityManager->getRepository(Fielddefinition::class)->
        createQueryBuilder('fielddefinition')
            ->select('count(fielddefinition.code)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function countFielddefinitionlang(): int
    {
        return  $this->entityManager->getRepository(Fielddefinitionlang::class)->
        createQueryBuilder('fielddefinitionlang')
            ->select('count(fielddefinitionlang.code)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function countFieldvaluecode(): int
    {
        return  $this->entityManager->getRepository(Fieldvaluecode::class)->
        createQueryBuilder('fieldvaluecode')
            ->select('count(fieldvaluecode.code)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
