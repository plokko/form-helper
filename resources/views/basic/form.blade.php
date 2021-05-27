<form
    action="{{$action}}"
    method="{{$method}}"
    >
    @csrf
    <input type="hidden" name="_method" value="{{$method}}" />

    @foreach($fields AS $field)
        @switch($field['type']??'text')
            @case('select')

                <label>{{$field['label']??''}}
                    <select
                        @foreach($field AS $k=>$v)
                            @if($k!=='value' && $k!=='label'&& $k!=='options')
                                {!! $k !!}="{{$v}}"
                            @endif
                        @endforeach
                        >
                        @if($field['items'])
                            @foreach($field['items'] AS $k=>$v)
                                <option value="{{$v}}">{{$k}}</option>
                            @endforeach
                        @endif
                    </select>
                </label>
            @case('textarea')
                <label>{{$field['label']??''}}
                    <textarea
                        @foreach($field AS $k=>$v)
                            @if($k!=='value' && $k!=='label')
                                {!! $k !!}="{{$v}}"
                            @endif
                        @endforeach

                        >{{optional($data)[$field['name']]}}</textarea>
                </label>
            @case('text')
            @default
                <label>{{$field['label']??''}}
                <input
                    @foreach($field AS $k=>$v)
                        @if($k!=='value' && $k!=='label')
                            {!! $k !!}="{{$v}}"
                        @endif
                    @endforeach
                    value="{{optional($data)[$field['name']]}}"
                    /></label>
        @endswitch
    @endforeach
    {{$slot ?? ''}}
    <button type="submit">@lang('form-helper::form.'.($data?'edit':'create').'.submit-btn')</button>
</form>
