<?php

namespace Plokko\FormHelper;


use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use Plokko\FormHelper\Traits\FormHelperFallbackTrait;

/**
 * Class FormField
 * @package plokko\FormHelper
 *
 * @method field(string $name):FormField Defines or returns a field
 * @method removeField(string $name):FormHelper Remove specified field
 * @method item($item):FormHelper Set form item
 * @method action(string $action='',string $method='post'):FormHelper Set form action and method
 * @method formView(string $view):FormHelper Set form template
 * @method render():string Renders the form
 *
 * @method label(string $type):$this
 * @extends \Plokko\FormHelper\FormHelper
 */
class FormField implements FormHelperInterface, JsonSerializable, IteratorAggregate, Arrayable
{
    use FormHelperFallbackTrait;
    protected
        $parent,
        $name,
        $attr=[],
        $validation=null,
        $autoValidate=true;

    function __construct(FormHelper $parent,$name){
        $this->parent = $parent;
        $this->name = $name;
    }


    function __call($fn,$args){
        if(in_array($fn,[
                'label',
            ])){
            $this->attr[$fn]=$args[0];
            return $this;
        }
        return call_user_func_array([$this->parent,$fn],$args);
    }

    /**
     * @param string $type
     * @return $this
     */
    function type($type='text'){
        $this->attr('type',$type);
        return $this;
    }

    /**
     * @param bool $required
     * @return $this
     */
    function required($required=true){
        $this->attr('required',$required);
        return $this;
    }

    function min($num){
        return $this->attr('min',$num);
    }
    function max($num){
        return $this->attr('max',$num);
    }

    function items(array $items,$itemValue='value',$itemText='text'){
        $this->attr['items']=$items;
        $this->attr['item-value']=$itemValue;
        $this->attr['item-text']=$itemText;
        return $this;
    }

    /**
     * @param string $key
     * @param null|mixed $value
     * @return $this
     */
    function attr($key,$value){
        if($value===null)
            unset($this->attr[$key]);
        else
            $this->attr[$key] = $value;
        return $this;
    }

    function attrs(array $attrs){
        foreach($attrs AS $k=>$v){
            $this->attr($k,$v);
        }
        return $this;
    }

    function clear(){
        $this->attr=[];
        return true;
    }
    //-----------
    function __get($k){
        switch($k){
            case 'name':
                return $this->$k;
            case 'value':
                return $this->getValue();
            default:
                return $this->attr[$k]??null;
        }
    }

    function getValue(){
        return $this->parent->valueOf($this->name);
    }

    public function toArray()
    {
        $data = array_merge($this->attr,[
            'name' => $this->name,
            //'value' => $this->getValue(),
        ]);
        return $data;
    }

    public function getIterator()
    {
        return collect($this->toArray());
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Set field validation array
     * @param string|null|string[] $validation Validation array for this field
     * @return $this
     */
    function fieldValidation($validation,$action=null){
        $this->validation = $validation;
        return $this;
    }

    /**
     * @return array|null
     */
    function getValidationArray($action=null){
        $rules = [];

        if(!$this->autoValidate){
            return $this->validation;
        }
        //-- auto validate and merge rules --//
        if($this->required) {
            //Auto field required
            $rules[] = 'required';
        }
        switch($this->type){
            case 'email':
            case 'file':
                $rules[] = $this->type;
                break;
            //case 'select':
            default:
        }
        /*
         * if($this->type==='select' && $this->items) {
            //Auto field required
            $rules[] = 'in:'.implode(',',$items);
        }*/
        foreach(['min','max'] AS $k){
            if(isset($this->attr[$k])){
                $rules[] = $k.':'.$this->attr[$k];
            }
        }
        if(!empty($this->attr['items'])){
            $values = array_map(function($e){ return $e[$this->attr['item-value']]; },$this->attr['items']);
            $rules[] = Rule::in($values);
        }

        if($this->validation!=null){
            $v = is_array($this->validation)?$this->validation:explode('|',$this->validation);
            $rules = array_unique(array_merge($rules,$this->validation));
        }
        return $rules;
    }

    /**
     * Closes field declaration and return to FormHelper
     * @return FormHelper
     */
    public function endField(){
        return $this->parent;
    }
    public function autoValidate($autovalidate=true){
        $this->autoValidate = $autovalidate;
        return $this;
    }
}
