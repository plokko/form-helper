<?php
namespace Plokko\FormHelper;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use IteratorAggregate;
use JsonSerializable;

class FormHelper implements JsonSerializable, IteratorAggregate, Arrayable, Renderable
{
    protected
        $action='',$method='post',
        $formView=null,
        $item = null,
        /**
         * @var FormField[]
         */
        $fields = [],
        $validations=[];

    function __construct(){}

    /**
     * Set item data
     * @param $item
     * @return $this
     */
    public function item($item){
        $this->item=$item;
        return $this;
    }

    /**
     * Set form action and method
     * @param $action
     * @param string $method
     */
    public function action($action='',$method='post'){
        $this->action=$action;
        $this->method=$method;
        return $this;
    }

    /**
     * Defines or returns a new field
     * @param string $name Field name
     * @return FormField
     */
    public function field($name){
        if(!isset($this->fields[$name])){
            $this->fields[$name] = new FormField($this,$name);
        }
        return $this->fields[$name];
    }

    /**
     * Remove a specified field
     * @param string $name Field name
     * @return $this
     */
    public function removeField($name){
        unset($this->fields[$name]);
        return $this;
    }

    /**
     * @param string $view
     * @return $this
     */
    public function formView($view){
        $this->formView = $view;
        return $this;
    }
    /**
     * @return string
     */
    public function render()
    {
        $view = config('form-helper.form-template','form-helper::form');
        return view($view,$this->toArray());
    }

    /**
     * @param string $name Field name
     * @return null|mixed
     */
    public function valueOf($name){
        if(!$this->item)
            return null;

        $v = $this->item;
        $split = explode('.',$name);
        foreach($split AS $k){
            if($k=='*')
                return $v;
            if(!isset($v->k))
                return null;
            $v = $v->k;
        }
        return $v;
    }
    //-----------
    public function toArray()
    {
        return [];
    }

    public function getIterator()
    {
        return collect($this->toArray());
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function labelsFromTrans($key){
        $tr = trans($key);
        if($tr){
            foreach($tr AS $k=>$v){
                if(isset($this->fields[$k])) {
                    $this->fields[$k]->label($v);
                }
            }
        }
    }

    public function __toString(){
        return json_encode($this);
    }

    /**
     * @return bool
     */
    public function isEdit(){
        return !!$this->item;
    }


    /**
     * Set validation rules for the form
     * @param array $rules
     * @param null|"edit"|"create" $action
     * @return $this
     */
    public function validations($rules,$action=null){
        $this->validations[$action] = $rules;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidationArray(){
        $rules = $this->validations[null]??[];
        $action = $this->isEdit()?'edit':'create';
        if(isset($this->validations[$action])){
            $rules = array_merge($this->validations[$action]);
        }
        //field validations
        foreach($this->fields AS $f){
            $v = $f->getValidationArray($action);
            if(count($v)>0){
                if(isset($rules[$f->name])){
                    //Merge rules
                    $rules[$f->name] = array_unique(array_merge($v,is_string($rules[$f->name])?explode('|',$rules[$f->name]):$rules[$f->name]));
                }else{
                    $rules[$f->name] = $v;
                }
            }
        }

        return $rules;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validate(Request $request){
        return $request->validate($this->getValidationArray());
    }
}
