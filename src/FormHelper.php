<?php
namespace Plokko\FormHelper;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use IteratorAggregate;
use JsonSerializable;

class FormHelper implements FormHelperInterface, JsonSerializable, IteratorAggregate, Arrayable//, Renderable
{
    protected
        $action='',$method='post',
        $formView=null,
        $data = null,
        /**
         * @var FormField[]
         */
        $fields = [],
        $validations=[],
        $autoValidations = true;

    function __construct(){}

    /**
     * Set item data
     * @param $data
     * @return $this
     */
    public function fill($data){
        $this->data=$data;
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
        $data = $this->toArray();
        $data['components'] = config('form-helper.components');
        return view($view,$data);
    }

    public function renderFormAttr()
    {
        $data = $this->toArray();
        $data['components'] = config('form-helper.components',null);
        return view('form-helper::form-attributes',$data);
    }

    /**
     * @param string $name Field name
     * @return null|mixed
     */
    public function valueOf($name){
        if(!$this->data)
            return null;

        $v = $this->data;
        $split = explode('.',$name);
        foreach($split AS $k){
            if($k=='*')
                return $v;
            if(!isset($v[$k]))
                return null;
            $v = $v[$k];
        }
        return $v;
    }

    /**
     * @return array
     */
    function getFieldsData(){
        $fields = [];
        foreach($this->fields AS $field){
            $fields[]=$field->toArray();
        }
        return $fields;
    }

    //-----------
    public function toArray()
    {
        return [
            'action' => $this->action,
            'method' => $this->method,
            'fields' => $this->getFieldsData(),
            'data' => $this->data,
        ];
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
        return !!$this->data;
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
        $action = $this->isEdit()?'edit':'create';

        $rules = $this->validations[null]??[];
        if(isset($this->validations[$action])){
            $rules = array_merge($this->validations[$action]);
        }

        if(!$this->autoValidations){
            return $rules;
        }


        //field validations
        foreach($this->fields AS $f){
            /**@var FormField $f**/
            $v = $f->getFieldValidationArray($action);

            self::deepMergeRules($rules,$v);
        }

        return $rules;
    }

    private static function deepMergeRules(array &$base, $newRules){
        if(!empty($newRules)){
            foreach($newRules AS $ruleName=> $newRule){
                if(isset($base[$ruleName])){
                    $base[$ruleName] = array_unique(array_merge($base[$ruleName],$newRule));
                }else{
                    $base[$ruleName] = $newRule;
                }
            }
        }
        return $base;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validate(Request $request){
        return $request->validate($this->getValidationArray());
    }

    /**
     * Enable or disable ALL the field auto generated validations
     * @param bool $enable
     * @return $this
     */
    public function autoValidations($enable=true){
        $this->autoValidations = $enable;
        return $this;
    }
}
