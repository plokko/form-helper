<?php
namespace Plokko\FormHelper;

use IteratorAggregate;
use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rule;
use Plokko\FormHelper\Traits\FormHelperFallbackTrait;

/**
 * Class FormField
 * @package plokko\FormHelper
 *
 *
 * @property-read string $name
 * @property-read null|mixed $value
 */
class FormField implements FormHelperInterface, JsonSerializable, IteratorAggregate, Arrayable
{
    use FormHelperFallbackTrait;
    protected
        $parent,
        $name,
        $type='text',
        $label = null,
        $component = null,
        $visible = true,
        $attr=[],

        $validation=[],
        $autoValidate=true;

    function __construct(FormHelper $parent,$name){
        $this->parent = $parent;
        $this->name = $name;
    }

    /**
     * Set field type
     * @param string $type Field type (ex. email, select, text, textarea, etc.)
     * @return $this
     */
    function type($type='text'){
        $this->type = $type;
        return $this;
    }

    /**
     * Set field label
     * @param string|null $label
     * @return $this
     */
    function label($label){
        $this->label = $label;
        return $this;
    }

    /**
     * Set field component
     * @param string|null $component component name
     * @return $this
     */
    function component($component){
        $this->component = $component;
        return $this;
    }

    /**
     * Set the field as required
     * @param bool $required
     * @return $this
     */
    function required($required=true){
        $this->attr('required',$required);
        return $this;
    }

    /**
     * Set if the field will be visible.
     * If set to null or true the field will be visible, if false will be hidden.
     * The field also accept a Javascript code with a boolean value;
     * the
     * @param boolean|string|null $visible
     * @return $this
     */
    function visible($visible=true){
        $this->visible = $visible;
        return $this;
    }

    /**
     * Set the field as multiple
     * @param bool $multiple
     * @return $this
     */
    function multiple($multiple=true){
        return $this->attr('multiple',$multiple);
    }

    /**
     * Set the field min length or number
     * @param integer $num
     * @return $this
     */
    function min($num){
        return $this->attr('min',$num);
    }

    /**
     * Set the field max length or number
     * @param integer $num
     * @return $this
     */
    function max($num){
        return $this->attr('max',$num);
    }

    /**
     * Specify allowed items in field (for select)
     * @param array|iterable $items
     * @param string $itemValue
     * @param string $itemText
     * @return $this
     */
    function items($items,$itemValue='value',$itemText='text'){
        $this->attr['items'] = $items;
        $this->attr['item-value'] = $itemValue;
        $this->attr['item-text'] = $itemText;
        return $this;
    }

    /**
     * Set field attribute
     * @param string $key attribute name
     * @param null|mixed $value attribute value
     * @return $this
     */
    function attr($key,$value){
        if($value===null)
            unset($this->attr[$key]);
        else
            $this->attr[$key] = $value;
        return $this;
    }

    /**
     * Set field attributes
     * @param array $attrs
     * @return $this
     */
    function attrs(array $attrs){
        foreach($attrs AS $k=>$v){
            $this->attr($k,$v);
        }
        return $this;
    }

    /**
     * Clear field attributes
     * @return $this
     */
    function clear(){
        $this->attr=[];
        return $this;
    }

    /**
     * Enable or disable field auto validation
     * @param bool $autovalidate
     * @return $this
     */
    public function autoValidate($autovalidate=true){
        $this->autoValidate = $autovalidate;
        return $this;
    }

    /**
     * Set field validation array
     * @param string|null|string[] $validation Validation array for this field
     * @return $this
     */
    function fieldValidation($validation,$action=null){
        $this->validation[$action] = is_string($validation)?explode('|',$validation):$validation;
        return $this;
    }

    //-----------

    function __call($fn,$args){
        /**
        if(in_array($fn,[])){
            $this->attr[$fn]=$args[0];
            return $this;
        }
        */
        return call_user_func_array([$this->parent,$fn],$args);
    }

    function __get($k){
        switch($k){
            case 'name':
            case 'label':
            case 'component':
                return $this->$k;
            case 'value':
                return $this->getValue();
            default:
                return $this->attr[$k]??null;
        }
    }

    /**
     * Return field value
     * @return mixed|null
     */
    function getValue(){
        return $this->parent->valueOf($this->name);
    }

    /**
     * @return array
     */
    function getAttr(){
        $attr = $this->attr;
        $attr['type'] = $this->type;
        return $attr;
    }

    /**
     * Cast to array of field proprieties
     * @return array
     */
    public function toArray()
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'attr' => $this->getAttr(),
            //'value' => $this->getValue(),
        ];
        if($this->visible!==true)
            $data['visible'] = $this->visible;
        if($this->label)
            $data['label'] = $this->label;
        if($this->component)
            $data['component'] = $this->component;

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
     * Return validation array
     * @param "edit"|"create" $action
     * @return array|null
     */
    function getFieldValidationArray($action){
        $fieldName = $this->name .($this->isMultiple()?'.*':'');
        $fieldRules = [];

        // Merge base with action rules
        $definedRules = (empty($this->validation[null])&&empty($this->validation[$action]))?
                            null:
                            array_merge($this->validation[null]??[],$this->validation[$action]??[]);

        // If autovalidation is disabled for this field return only defined validations
        if(!$this->autoValidate){
            return $definedRules?[$fieldName => $definedRules]:null;
        }

        //-- auto validate and merge rules --//
        if($this->required) {
            $fieldRules[] = 'required';
        }
        switch($this->type){
            case 'email':
            case 'file':
                $fieldRules[] = $this->type;
                break;
            //case 'select':
            default:
        }

        if(!$this->isMultiple()){
            foreach(['min','max'] AS $k){
                if(isset($this->attr[$k])){
                    $fieldRules[] = $k.':'.$this->attr[$k];
                }
            }
        }

        if(!empty($this->attr['items'])){
            $values = array_map(function($e){ return $e[$this->attr['item-value']]; },$this->attr['items']);
            $fieldRules[] = Rule::in($values);
        }

        // Merge auto validations with field validations
        if(!empty($definedRules)){
            $fieldRules = array_unique(array_merge($fieldRules,$definedRules));
        }
        ///-----
        $rules = [];
        if(!empty($fieldRules)){
            $rules[$fieldName] = $fieldRules;
        }

        ///-- Base field rules for array ---//
        if($this->isMultiple()){
            $rootFieldRule = ['array',];
            if($this->required) {
                $rootFieldRule[] = 'required';
            }

            foreach(['min','max'] AS $k){
                if(isset($this->attr[$k])){
                    $rootFieldRule[] = $k.':'.$this->attr[$k];
                }
            }
            $rules[$this->name] = $rootFieldRule;
        }
        //--

        return count($rules)>0?$rules:null;
    }

    /**
     * Closes field declaration and return to FormHelper
     * @return FormHelper
     */
    public function endField(){
        return $this->parent;
    }

    public function isMultiple(){
        return !!$this->multiple;
    }

}
