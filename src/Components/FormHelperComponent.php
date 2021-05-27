<?php
namespace Plokko\FormHelper\Components;

use Illuminate\View\Component;
use Plokko\FormHelper\FormHelperInterface;

class FormHelperComponent extends Component
{
    private
        $form,
        $template;

    /**
     * FormHelperComponent constructor.
     * @param FormHelperInterface $form
     * @param null|string $template
     */
    public function __construct(FormHelperInterface $form,$template=null)
    {
        $this->form = $form;
        $this->template = $template;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        $view = $this->template??config('form-helper.form-template','form-helper::form');
        $data = $this->form->toArray();
        return view($view,$data);
    }
}
