<?php
namespace App\GptCavebackendBundle\Util;
/**
 * Suggest array
 */
class Select2
{

    /**
     * DB array 
     * @var array
     */
    protected $result;

    public function __construct($result){
        $this->result = $result;
    }

    /**
     * array para select2
     * @param string $id identificador
     * @param string $value texto del selector
     * @return array
     */
    public function getArray($id, $value)
    {        
        $a = [];//block array
        if(!empty($this->result)){
            foreach($this->result as $r){
                if(gettype($r)== 'object'){
                     $block = array('id'=>$r->{'get'.  ucfirst($id)}(), 'text'=>$r->{'get'.ucfirst($value)}());
                }else{
                    $block = array('id'=>$r[$id], 'text'=>$r[$value]);
                }

                $a[] = $block;
            }
        } 
        return $a;
    }

    /**
     * array para select2 con resultados
     * @param string $primaryKey primary key
     * @param string $format vprintf format
     * @param array $values array de valores a buscar
     * @return array
     */
    public function getVsprintfArray($primaryKey, $format, $values)
    {        
        $a = [];//block array
        if(!empty($this->result)){
            foreach($this->result as $r){
                if(gettype($r)== 'object'){
                    $args = [];
                    foreach ($values as $v){
                        $args[$v] = $r->{'get'.ucfirst($v)}();
                    }
                    $block = array('id'=>$r->{'get'.  ucfirst($primaryKey)}(), 'text'=>vsprintf($format, $args));
                }else{
                    $args = [];                    
                    foreach ($values as $v){
                        $args[$v] = $r[$v];
                    }        
                    $block = array('id'=>$r[$primaryKey], 'text'=>vsprintf($format, $args));
                }
                $a[] = $block;
            }

        } 
        return $a;
    }    

}
