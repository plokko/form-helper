<form-helper
    action="{{$action}}"
    method="{{$method}}"
    :fields="{{json_encode($fields)}}"
    :value="{{json_encode($data)}}"
    submit-text="{{trans('form-helper::form.'.($data?'edit':'create').'.submit-btn')}}"
    >
    {{$slot ?? ''}}
</form-helper>
