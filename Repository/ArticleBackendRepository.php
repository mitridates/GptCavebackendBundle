<?php

namespace App\GptCavebackendBundle\Repository;
use App\GptCaveBundle\Entity\Article;
use App\GptCaveBundle\Doctrine\Paginator;
use Doctrine\{ORM\EntityManagerInterface, ORM\EntityRepository, ORM\NonUniqueResultException, ORM\Query\Expr};

class ArticleBackendRepository extends EntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Article::class);
    }
    
    /**
     * Find by entity
     * @param Article $article
     * @param int $page current page
     * @param int $ipp Items per page
     * @return array [Paginator, [result]]
     * @throws NonUniqueResultException
     */
    public function pageByArticle(Article $article, $page, $ipp)
    {
        $alias = 'art';
        $qb = $this->repository->createQueryBuilder($alias);
        $expr = new Expr();
        $andx = $expr->andX();
        $params = [];

        //name LIKE value
        foreach(['publicationyear', 'author', 'articleid', 'name'] as $k)
        {
            if($article->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->like($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = '%'.$article->{'get'.  ucfirst($k)}().'%';
            }

        }

        //name = value
        foreach(['country','admin1'] as $k)
        {
            if($article->{'get'.  ucfirst($k)}()!=null)
            {
                $andx->add($expr->eq($alias.'.'.$k, ':'.$k));
                $params[':'.$k] = $article->{'get'.  ucfirst($k)}();
            }
        }

        //WHERE
        if($andx->count()>0){
            $qb->add('where', $andx)->setParameters($params);
        }

        /**
         * SELECT to count results
         */
        $totalRows = $qb->add('select',$expr->count($alias.'.articleid'))->getQuery()->getSingleScalarResult();

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
        $alias = 'art';
        $qb = $this->repository->createQueryBuilder($alias)
            ->select([$alias.'.articleid',$alias.'.name'])
            ->orderBy($alias.'.name','ASC');

        if(!empty($string))
        {
            $qb->where($qb->expr()->like($alias.'.name', ':name'))
                ->setParameter(':name' , '%'.$string.'%');
        }
        return $qb->getQuery()->getArrayResult();
    }
}
