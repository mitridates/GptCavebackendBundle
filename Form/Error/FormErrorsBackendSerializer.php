<?php
namespace App\GptCavebackendBundle\Form\Error;
use App\GptCavebackendBundle\Repository\FielddefinitionBackendRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\Form;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormErrorsBackendSerializer
{
    /**
     * @var FielddefinitionBackendRepository
     */
    private $fielddefinitionBackendRepository;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    /**
     * @param FielddefinitionBackendRepository $fielddefinitionBackendRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(FielddefinitionBackendRepository $fielddefinitionBackendRepository, TranslatorInterface $translator)
    {
        $this->fielddefinitionBackendRepository = $fielddefinitionBackendRepository;
        $this->translator = $translator;
    }

    /**
     * Serialize and translate Form Error messages
     *      Translate field name from Fielddefinition repository if 'attr'=>['code_id'=>xxx] exists in field options.
     *      Translate global and child message using Symfony Translation component.
     *
     * @example
     *      new FormError('Message if no translation available ', null, [
     *              'trans'=>['form.field.circular.reference.error', [], 'caveerrors']
     *          ;])
     * @param Form $form
     * @param string|null $locale Session/Request locale
     * @return array
     * @throws NonUniqueResultException
     */
    public function translateFormErrors(Form $form, string $locale): array
    {
        $errors = array();
        $errors['global'] = array();
        $errors['fields'] = array();
        $errors['form'] = $form->getName();
        $errors['flat'] = [];
        /**
         * GLOBAL ERRORS
         */
        foreach ($form->getErrors() as $error)//GLOBAL FORM ERRORS
        {

            $errors['global'][] = [
                'name'=>null,
                'message'=>$this->translateFromParameters($error->getMessage(), $error->getMessageParameters()),
                'parameters'=>$error->getMessageParameters(),
            ];
            $errors['flat'][]= $this->translateFromParameters($error->getMessage(), $error->getMessageParameters());
        }

        /**
         * CHILD(FIELD) ERRORS
         */
        $child_errors= [];
        foreach ($form->getIterator() as $key => $child)//FORM FIELD ERRORS
        {

            foreach ($child->getErrors() as $e)
            {
                $current_error =[
                    'name'=>$key,
                    'field'=>$key,
                    'message'=>$this->translateFromParameters($e->getMessage(), $e->getMessageParameters()),
                    'parameters'=>$e->getMessageParameters(),
                ];

                /**
                 * Use getTranslationORFielddefinition for field "name" if code_id attr exists
                 */
                $codeId = $form->get($key)->getConfig()->getOptions()['attr']['code_id'] ?? null;
                if($codeId && $fd = $this->fielddefinitionBackendRepository->getTranslationORFielddefinition($codeId, $locale))
                {
                    $current_error = array_replace_recursive($current_error, [
                     'name'=>$fd->getName(),
                     'code_id'=>$codeId,
                     'locale'=>$locale,
                    ]);
                }
                $errors['flat'][]= $current_error['name']. ': '. $current_error['message'];
                $child_errors[]= $current_error;
            }

            $errors['fields']= $child_errors;

        }

        return $errors;
    }

    /**
     * @param string $default
     * @param array $parameters
     * @return string
     */
    private function translateFromParameters(string $default, array $parameters): string
    {
            if(!isset($parameters['trans'])) return $default;
            $message = call_user_func_array([$this->translator, 'trans'], $parameters['trans']);
            return ($message == $parameters['trans'][0])? $default : $message;
    }
}