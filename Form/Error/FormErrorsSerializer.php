<?php
namespace App\GptCavebackendBundle\Form\Error;
use \Symfony\Component\Form\Form;

class FormErrorsSerializer
{

    /**
     * @param Form $form
     * @param bool $flat_array
     * @param bool $add_form_name
     * @param string $glue_keys
     * @return array
     */
    public static function serializeFormErrors(Form $form, $flat_array = false, $add_form_name = false, $glue_keys = '_')
    {
        $errors = array();
        $errors['global'] = array();
        $errors['fields'] = array();
        $errors['form'] = $form->getName();
        
        foreach ($form->getErrors() as $error) {
            $errors['global'][] = $error->getMessage();
        }

        $errors['fields'] = self::serialize($form);
        if ($flat_array) {
            $errors['fields'] = self::arrayFlatten($errors['fields'],
                $glue_keys, (($add_form_name) ? $form->getName() : ''));
        }
        $errors['count'] = count($errors['global'])+count($errors['fields']);
        return $errors;
    }

    /**
     * @param Form $form
     * @return array
     */
    protected static function serialize(Form $form)
    {
        $local_errors = array();
        foreach ($form->getIterator() as $key => $child) {

            foreach ($child->getErrors() as $error){
                $local_errors[] = [
                    'name'=>$key,
                    'message'=>$error->getMessage(),
                    'parameters'=>$error->getMessageParameters()
                ];
            }

            if (count($child->getIterator()) > 0 && ($child instanceof Form)) {
                $local_errors = array_merge($local_errors, self::serialize($child));
            }
        }

        return $local_errors;
    }

    /**
     * @param $array
     * @param string $separator
     * @param string $flattened_key
     * @return array
     */
    private static function arrayFlatten($array, $separator = "_", $flattened_key = '') {
        $flattenedArray = array();
        foreach ($array as $key => $value) {

            if(is_array($value)) {

                $flattenedArray = array_merge($flattenedArray,
                    self::arrayFlatten($value, $separator,
                        (strlen($flattened_key) > 0 ? $flattened_key . $separator : "") . $key)
                );

            } else {
                $flattenedArray[(strlen($flattened_key) > 0 ? $flattened_key . $separator : "") . $key] = $value;
            }
        }
        return $flattenedArray;
    }

}