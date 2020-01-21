<?php
namespace App\GptCavebackendBundle\Form\Error;
use App\GptCavebackendBundle\Repository\FielddefinitionBackendRepository;
use App\GptCaveBundle\Entity\Fielddefinition;
use App\GptCaveBundle\Repository\FielddefinitionRepository;
use Symfony\Component\Form\Form;

class FormErrorsFielddefinitionSerializer  extends  FormErrorsSerializer
{
    /**
     * Search attr[code_id] (Fielddefinition PK), get from DB and Add data to errors
     * @param Form $form
     * @param FielddefinitionRepository $repository
     * @param string $locale Locale iso 2 caracteres
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public static function serializeFielddefinitions(Form $form, FielddefinitionBackendRepository $repository, $locale=null)
    {
        $error = self::serializeFormErrors($form);

        if(empty($error['fields'])) return $error;

        $local_errors = array();
        foreach ($form->getIterator() as $key => $child) {

            foreach ($child->getErrors() as $e){
                $attr = $form->get($key)->getConfig()->getOptions()['attr'];
                $current = [
                    'name'=>$key,
                    'msg'=>$e->getMessage(),
                ];
               // var_dump($e->getMessageParameters());
                if(isset($attr ['code_id'])){
                    $fd = $repository->getFielddefinitionRepository()->find($attr['code_id']);

                    if($fd instanceof Fielddefinition)
                    {
                        $trans = ($fd!=null)? $repository->getTranslationORFielddefinition($fd->getCode(), $locale): null;
                        $current['name']= $fd->getName();
                        $current['code']= $fd->getCode();
                        $current['trans']= $e->getMessageParameters()['trans'] ?? null;
                        //$current['entity']= $fd->getEntity();
                        //$current['datatype']= $fd->getDatatype();
                        //$current['valuecode']= $fd->getFieldvaluecode();
                        if($trans){
                            $current['name']= $trans->getName();
                            $current['data-lang'] =  gettype($locale).'-'.$locale;
                            $current['data-trans'] =  gettype($trans);
                        }
                    }
                }
                $local_errors[] = $current;
            }

            //TODO: test?
            if (count($child->getIterator()) > 0 && ($child instanceof Form)) {
                $local_errors = array_merge($local_errors, self::serializeFielddefinitions($child, $repository, $locale));
            }
        }
        $error['fields'] = $local_errors;
        return $error;
    }

}