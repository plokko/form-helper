<form-helper
    action="{{$action}}"
    method="{{$method}}"
    :fields="{{json_encode($fields)}}"
    :value="{{json_encode($data)}}"
    @if($components)
    :components="{{json_encode($components)}}"
    @endif
    submit-text="{{trans('form-helper::form.'.($data?'edit':'create').'.submit-btn')}}"
    >{{$slot ?? ''}}</form-helper>
