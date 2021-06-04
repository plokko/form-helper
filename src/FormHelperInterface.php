<?php


namespace Plokko\FormHelper;


use Illuminate\Http\Request;
use Illuminate\View\View;

interface FormHelperInterface
{
    /**
     * Fills the form with specified data as field_name => field_value
     * @param $data
     * @return $this
     */
    public function fill($data);

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
     * Set the Blade View to use for the form render
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
     * Return the value of a field (by field name)
     * @param string $name Field name
     * @return null|mixed
     */
    public function valueOf($name);

    /**
     * Return fields data
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


    /**
     * Enable or disable ALL the field auto generated validations
     * @param bool $enable
     * @return $this
     */
    public function autoValidations($enable=true);
}
