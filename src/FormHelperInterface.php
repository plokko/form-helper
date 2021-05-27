<?php


namespace Plokko\FormHelper;


use Illuminate\Http\Request;
use Illuminate\View\View;

interface FormHelperInterface
{

    /**
     * @param $data
     * @return $this
     */
    public function data($data);

    /**
     * Set form action and method
     * @param $action
     * @param string $method
     */
    public function action($action='',$method='post');

    /**
     * Defines or returns a new field
     * @param string $name Field name
     * @return FormField
     */
    public function field($name);
    
    /**
     * Remove a specified field
     * @param string $name Field name
     * @return $this
     */
    public function removeField($name);

    /**
     * @param string $view
     * @return $this
     */
    public function formView($view);
    
    /**
     * Render the form
     * @return string|View
     */
    public function render();


    /**
     * Apply labels from translation key array
     * @param string $key
     * @return $this
     */
    public function labelsFromTrans($key);

    /**
     * Set validation rules for the form
     * @param array $rules
     * @param null|"edit"|"create" $action
     * @return $this
     */
    public function validations($rules,$action=null);

    /**
     * @param string $name Field name
     * @return null|mixed
     */
    public function valueOf($name);

    /**
     * Return field data
     * @return array
     */
    function getFieldsData();
    
    /**
     * Validate a request
     * @param Request $request
     * @return array validated data
     */
    public function validate(Request $request);
    
    /**
     * Returns true if is an edit form, false if it's a create form
     * @return bool
     */
    public function isEdit();

    /**
     * Return validation array
     * @return array
     */
    public function getValidationArray();
    
}
