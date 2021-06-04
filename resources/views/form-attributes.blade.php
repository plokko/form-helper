action="{{$action}}"
method="{{$method}}"
:fields="{{json_encode($fields)}}"
:value="{{json_encode($data)}}"
@if(!empty($components))
:components="{{json_encode($components)}}"
@endif
