<form-helper
    action="{{$action}}"
    method="{{$method}}"
    :fields="{{json_encode($fields)}}"
    :value="{{json_encode($data)}}"
    submit-text="{{trans('form-helper::form.'.($data?'edit':'create').'.submit-btn')}}"
    >
    <template v-slot:field.email="{field,value}">
        <v-text-field
            v-model="value"
            v-bind="field"
            outlined
        ></v-text-field>
    </template>
    <template v-slot:type.password="{field,value}">
        <v-text-field
            v-model="value"
            v-bind="field"
            solo
            ></v-text-field>
    </template>

</form-helper>
<pre>{!! var_export(compact('action','method','fields','data')) !!}</pre>
