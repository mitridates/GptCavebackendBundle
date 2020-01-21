<?php
namespace App\GptCavebackendBundle\Util\ControllerParameters;

use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BackendParams
{

    /**
     * @var array
     */
    protected $parametersbag=[];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name BackendParams Section name == Controller name
     * @param array $params Bundle parameters
     * @param TranslatorInterface $translator
     */
    public function __construct(string $name, array $params, TranslatorInterface $translator)
    {
        $this->name = $name;
        $this->translator = $translator;
        $this->parametersbag=[
            'bundle'=> $params,
            'router'=>[
                'home'=> ['text'=>'home'],
                'login'=> ['text'=>'login']
            ],
            'page'=>array_merge(
                ['section'=>$this->name],
                $params['section']['default'],
                $params['section'][$this->name] ?? $params['section']['default']

            )
        ];

        $this->init();
    }

    /**
     * @param string $name
     * @param array $crumb
     * @return $this
     */
    public function addCrumb(string $name, array $crumb): self
    {
        $this->parametersbag['page']['router'][$name]=
            array_merge([
                'text'=> 'textNotFound', //link text
                'title'=> false, //string link title
                'path'=> false //Router->generate()
            ], $crumb);
        return $this;
    }

    /**
     * @return array
     */
    public function getParametersbag(): array
    {
        return $this->parametersbag;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function add(string $name, $value): self
    {
        $this->parametersbag['page'][$name]=$value;
        return $this;
    }


    /**
     * Set basic section configuration
     * @return void
     */
    protected function init(): void
    {
        $this->addCrumb('index', [
            'text'=> $this->translator->trans($this->name.'.index.page.title',[], 'cavepages'),
            'path'=>'cave_backend_.'.$this->name.'._index'
        ])->addCrumb('new', [
            'text'=> $this->translator->trans($this->name.'.menu.new',[], 'cavepages'),
            'path'=>'cave_backend_.'.$this->name.'._new'
        ]);
    }
    
}