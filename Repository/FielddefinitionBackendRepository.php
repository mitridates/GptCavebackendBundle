<?php
namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Repository\ExprFilterTrait;
use Doctrine\Common\Persistence\ObjectRepository;
use App\GptCaveBundle\Entity\{Fielddefinition, Fielddefinitionlang, Fieldvaluecode};
use App\GptCaveBundle\Doctrine\Paginator;
use Doctrine\ORM\{AbstractQuery, EntityManagerInterface, NonUniqueResultException, Query\Expr};

class FielddefinitionBackendRepository
{
    use ExprFilterTrait;

    /**
     * @var ObjectRepository
     */
    private $fielddefinitionRepository;

    /**
     * @var ObjectRepository
     */
    private $fielddefinitionlangRepository;
    /**
     * @var ObjectRepository
     */
    private $fieldvaluecodeRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->fielddefinitionRepository = $entityManager->getRepository(Fielddefinition::class);
        $this->fielddefinitionlangRepository = $entityManager->getRepository(Fielddefinitionlang::class);
        $this->fieldvaluecodeRepository = $entityManager->getRepository(Fieldvaluecode::class);
    }

    /**
     * @return ObjectRepository
     */
    public function getFielddefinitionRepository(): ObjectRepository
    {
        return $this->fielddefinitionRepository;
    }

    /**
     * @return ObjectRepository
     */
    public function getFielddefinitionlangRepository(): ObjectRepository
    {
        return $this->fielddefinitionlangRepository;
    }

    /**
     * @return ObjectRepository
     */
    public function getFieldvaluecodeRepository(): ObjectRepository
    {
        return $this->fieldvaluecodeRepository;
    }
    
    /**
     * Find by entity
     * @param Fielddefinition $fielddefinition
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByFielDefinition(Fielddefinition $fielddefinition, $page, $ipp) : array
    {
        $alias = 'fd';
        $qb = $this->fielddefinitionRepository->createQueryBuilder($alias);
        $expr = new Expr();

        $this->addExprFilter($qb, $fielddefinition, $alias, [
                'like'=> ['code', 'name'],
                'eq'=>['entity','datatype','coding', 'singlemultivalued', 'valuecode']
            ]
        );

//        $andx = $expr->andX();
//        $params = [];
//
//        //name LIKE value
//        foreach(['code', 'name'] as $k)
//        {
//            if($fielddefinition->{'get'.  ucfirst($k)}()!=null)
//            {
//                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
//                $params[':'.$k] = '%'.$fielddefinition->{'get'.  ucfirst($k)}().'%';
//            }
//
//        }
//
//        //name = value
//        foreach(['entity','datatype', 'coding', 'singlemultivalued', 'valuecode'] as $k)
//        {
//            if($fielddefinition->{'get'.  ucfirst($k)}()!=null)
//            {
//                $andx->add($expr->eq($alias.'.'.$k, ':'.$k));
//                $params[':'.$k] = $fielddefinition->{'get'.  ucfirst($k)}();
//            }
//        }

//        //WHERE
//        if($andx->count()>0){
//            $qb->add('where', $andx)->setParameters($params);
//        }
        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.code'))->getQuery()->getSingleScalarResult();
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
     * Traducciones
     * @param int $code code
     * @param string $lang
     * @return Fielddefinitionlang|null
     * @throws NonUniqueResultException
     */
   public function getTranslation($code, $lang)
   {
       return $this->fielddefinitionlangRepository
           ->createQueryBuilder('q')
           ->select('q')
           ->where('q.code = :code')
           ->andWhere('q.language = :language')
           ->setParameter(':code', $code)
           ->setParameter(':language', $lang)
           ->getQuery()
           ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @param int $code
     * @param string|null $locale
     * @return Fielddefinition|null
     * @throws NonUniqueResultException
     */
    public function getTranslationORFielddefinition(int $code, ?string $locale): ? Fielddefinition
    {
        $translated = ($locale)? $this->getTranslation($code, $locale) : null;
        $fielddefinition = $this->fielddefinitionRepository->find($code);
        if($translated==null) return $fielddefinition;
        foreach (['name', 'abbreviation', 'definition', 'example', 'comment', 'uso'] as $item){
            if($translated->{'get'.  ucfirst($item)}() != null)
            {
                $fielddefinition->{'set'.  ucfirst($item)}($translated->{'get'.  ucfirst($item)}());
            }
        }
        return $fielddefinition;
    }

    /**
     * Find by code in translations
     * @param $code
     * @param int $page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function paginateFdTranslationsByCode($code, $page, $ipp){
        $alias = 'fdl';
        $qb = $this->fielddefinitionlangRepository->createQueryBuilder($alias);
        $qb->add('where', $qb->expr()->eq($alias.'.code', ':code'))->setParameter(':code', $code);


//        $qb = $this->fielddefinitionlangRepository
//            ->createQueryBuilder($alias)
//            ->from('App\GptCaveBundle\Entity\Fielddefinitionlang', $alias)
//            ->where($alias.'.code = :code')
//            ->setParameter(':code', $code);

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$qb->expr()->count($alias.'.code'))->getQuery()->getSingleScalarResult();


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
     * Search name and abbreviation
     * @param int $code Code Id
     * @param string|null $locale
     * @return array|null [name][abbreviation]
     * @throws NonUniqueResultException
     */
    public function getFielddefinitionName(int $code, $locale=null)
    {
        $qb= $this->fielddefinitionRepository->createQueryBuilder('q')
            ->select(['q.code', 'q.name', 'q.abbreviation'])
            ->where('q.code = :code')
            ->setParameter(':code', $code);

        if($locale==null){
            return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        }else{
            $name = $this->fielddefinitionlangRepository
                ->createQueryBuilder('l')
                ->select('l.name')
                ->where('l.language= :locale')
                ->andWhere('l.code= :code')
                ->getDQL()
            ;
            $abbreviation = $this->fielddefinitionlangRepository
                ->createQueryBuilder('l2')
                ->select('l2.abbreviation')
                ->where('l2.language= :locale')
                ->andWhere('l2.code= :code')
                ->getDQL()
            ;

            return $qb->addSelect('('.$name.') AS trans_name')
                ->addSelect('('.$abbreviation.') AS trans_abbreviation')
                ->setParameter(':locale', $locale)
                ->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        }

    }

    /**
     * Find by string for suggest
     * @param string $string
     * @return array
     */
    public function filterByString($string)
    {
        $alias = 'fd';
        $qb = $this->fielddefinitionRepository->createQueryBuilder($alias);
        $selects = (new Expr\Select())->addMultiple(
            [
                $alias.'.name',
                $alias.'.entity',
                $alias.'.code'
            ]
        );

        $qb->add('select', $selects)
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
                $qb->add('where', $qb->expr()->like($alias.(is_numeric($string)? '.code' : '.name'), ':string'))
                    ->setParameter(':string' , '%'.$string.'%');
        }

        return $qb->getQuery()->getArrayResult();
    }


    public function test($code, $locale){

       $name = $this->fielddefinitionRepository
            ->createQueryBuilder('l')
            ->from(Fielddefinitionlang::class, 'l')
            ->select('l.name')
            ->where('l.language= :locale')
            ->andWhere('l.code= :code')
            ->getDQL()
        ;
        $abbreviation = $this->fielddefinitionRepository
            ->createQueryBuilder('l2')
            ->from(Fielddefinitionlang::class, 'l2')
            ->select('l2.abbreviation')
            ->where('l2.language= :locale')
            ->andWhere('l2.code= :code')
            ->getDQL()
        ;

        $qb= $this->fielddefinitionRepository->createQueryBuilder('q')
            ->select(['q.code', 'q.name', 'q.abbreviation'])
            ->addSelect('('.$name.') AS t_name')
            ->addSelect('('.$abbreviation.') AS t_abbreviation')
            ->where('q.code = :code')
            ->setParameter(':code', $code)
            ->setParameter(':locale', 'es')

        ;


            var_dump($qb->getQuery()->getResult(2));
/*            var_dump($qb->getQuery()->getDQL());
            return $qb->getQuery()->getResult(2);*/



        //---------------------TEST
    }
}
