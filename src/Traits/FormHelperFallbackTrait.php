<?php

namespace Plokko\FormHelper\Traits;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Plokko\FormHelper\FormField;
use Plokko\FormHelper\FormHelper;
use Plokko\FormHelper\FormHelperInterface;

trait FormHelperFallbackTrait
{

    /**
     * Fill form data
     * @param $data
     * @return $this
     */
    public function fill($data){
		$this->parent->data($data);
		return $this;
	}

    /**
     * Set form action and method
     * @param $action
     * @param string $method
     */
    public function action($action='',$method='post'){
		$this->parent->action($action='',$method='post');
		return $this;
	}

    /**
     * Defines or returns a new field
     * @param string $name Field name
     * @return FormField
     */
    public function field($name){
        return $this->parent->field($name);
	}

    /**
     * Remove a specified field
     * @param string $name Field name
     * @return FormHelper
     */
    public function removeField($name){
        return $this->parent->removeField($name);
	}

    /**
     * @param string $view
     * @return $this
     */
    public function formView($view){
		$this->parent->formView($view);
		return $this;
	}

    /**
     * Render the form
     * @return string|View
     */
    public function render(){
		$this->parent->render();
		return $this;
	}


    /**
     * Apply labels from translation key array
     * @param string $key
     * @return $this
     */
    public function labelsFromTrans($key){
		$this->parent->labelsFromTrans($key);
		return $this;
	}

    /**
     * Set validation rules for the form
     * @param array $rules
     * @param null|"edit"|"create" $action
     * @return $this
     */
    public function validations($rules,$action=null){
		$this->parent->validations($rules,$action=null);
		return $this;
	}

    /**
     * @param string $name Field name
     * @return null|mixed
     */
    public function valueOf($name){
		$this->parent->valueOf($name);
		return $this;
	}

    /**
     * Return field data
     * @return array
     */
    function getFieldsData(){
		$this->parent->getFieldsData();
		return $this;
	}

    /**
     * Validate a request
     * @param Request $request
     * @return array validated data
     */
    public function validate(Request $request){
		$this->parent->validate($request);
		return $this;
	}

    /**
     * Returns true if is an edit form, false if it's a create form
     * @return bool
     */
    public function isEdit(){
		$this->parent->isEdit();
		return $this;
	}

    /**
     * Return validation array
     * @return array
     */
    public function getValidationArray(){
		$this->parent->getValidationArray();
		return $this;
	}
}
