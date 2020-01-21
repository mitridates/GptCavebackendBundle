<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;

use App\GptCavebackendBundle\Repository\SetupBackendRepository;
use App\GptGeonamesBundle\Entity\Admin1;
use App\GptGeonamesBundle\Entity\Admin2;
use App\GptGeonamesBundle\Entity\Admin3;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Translation\TranslatorInterface;

class SetupParams extends BackendParams
{
    /**
     * @param ObjectManager $em
     * @return array
     * @throws NonUniqueResultException
     */
    public function indexParams(ObjectManager $em): array
    {
        $sbr= new SetupBackendRepository($em);
        return $this
            ->add('countries', $sbr->getCountries())
            ->add('organisation', $sbr->countOrganisation())
            ->add('fielddefinition', $sbr->countFielddefinition())
            ->add('fielddefinitionlang', $sbr->countFielddefinitionlang())
            ->add('fieldvaluecode', $sbr->countFieldvaluecode())
            ->add('name', 'index')
            ->add('title', $this->translator->trans('setup.index.page.title',[], 'cavepages'))
            ->add('breadcrumb', ['index'])
            ->getParametersbag()
           ;
    }

    /**
     * @param ObjectManager $em
     * @return array
     */
    public function countryParams(ObjectManager $em): array
    {
        return $this
            ->add('countries', (new SetupBackendRepository($em))->getCountries())
            ->add('title', $this->translator->trans('setup.index.page.title',[], 'cavepages'))
            ->add('breadcrumb', ['index'])
            ->getParametersbag()
            ;
    }

    /**
     * @param ObjectManager $em
     * @return array
     */
    public function organisationParams(ObjectManager $em): array
    {
        return $this
            ->add('organisations', (new SetupBackendRepository($em))->countOrganisation())
            ->add('name', 'index')
            ->add('title', $this->translator->trans('setup.index.page.title',[], 'cavepages'))
            ->add('breadcrumb', ['index'])
            ->getParametersbag()
            ;
    }
}