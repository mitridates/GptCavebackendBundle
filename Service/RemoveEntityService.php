<?php
namespace App\GptCavebackendBundle\Service;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Lookup Sysparam to Delete/hide/backup registry.
 *
 * @package App\GptCavebackendBundle\Service
 */
class RemoveEntityService
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var Arraypath %cave_backend.ondelete% parameter
     */
    protected $ondelete;

    public function __construct(EntityManager $em, array $parameters)
    {
        $this->em= $em;
        $this->ondelete= $parameters['ondelete'] ? : [];
    }

    /**
     * Backup
     * @todo Make backup in other table
     * @param $entity
     */
    private function makeBackup($entity)
    {

    }

    /**
     * Oculta la entidad
     * @param $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function makeHidden($entity)
    {
        $entity->setHidden(true);
        $this->em->persist($entity);
        $this->em->flush($entity);
    }

    /**
     * Borra la entidad
     * @param $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function makeDelete($entity)
    {
            $this->em->remove($entity);
            $this->em->flush($entity);
    }


    /**
     * Borra/oculta/backup de la entidad borrada en su caso según preferencias.
     * @param $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete($entity){
        $remove = $this->ondelete['remove']? : false;
        $backup = $this->ondelete['backup']? : true;
        if($remove){
            if($backup) $this->makeBackup($entity);
            $this->makeDelete($entity);
        }else{
            $this->makeHidden($entity);
        }
    }
}