<template>
    <v-form ref="form" v-model="validForm">
        <v-expand-transition>
            <v-alert type="error" ref="error" v-show="!!error">{{errorMessage}}</v-alert>
        </v-expand-transition>
        <slot name="items" v-bind="{items:fieldDataList}">
            <template v-for="{field} in fieldDataList">
                <form-helper-field
                    :field="field"
                    ><template
                        v-slot:item="slotData"
                        ><slot
                            :name="'field.'+field.name"
                            v-bind="slotData"
                            ><slot
                                :name="'type.'+(field.type||'text')"
                                v-bind="slotData"
                                ></slot></slot></template></form-helper-field>
            </template>
        </slot>
        <slot></slot>
        <slot name="submit" v-bind="{submit,canSubmit,loading}">
            <v-btn
                @click="submit"
                :disabled="!canSubmit"
                :loading="loading"
                color="primary"
                ><v-icon left>send</v-icon> {{submitText}}</v-btn>
        </slot>
    </v-form>
</template>
<script>
import FormHelperField from './FormHelperField';
import BaseFormHelper from '../FormHelper';
export default {
    name: 'VuetifyFormHelper',
    extends: BaseFormHelper,
    computed:{
        computedFields(){
            return this.fields.map(field=>{
                let name = field.name;

                if(!field.component)
                    field.component = this.componentList[field.type] || this.componentList['text'];

                // Parse attributes starting with ":"
                for(let k in field.attr){
                    if(k[0]===':'){
                        try{
                            field.attr[k.substr(1)] = eval(field.attr[k]);
                        }catch(e){
                            console.error('field "'+k+'" evaluation error:',e);
                        }
                    }else{
                        field.attr[k]=field.attr[k];
                    }
                }
                //
                if(this.errors && this.errors[name]){
                    field.attr['error-messages'] = field.errors = this.errors[name];
                }

                for(let k of ['label','item-text','item-value']){
                    if(field[k])
                        field.attr[k] = field[k];
                }

                if(field.items){
                    if(typeof field.items  === 'object'){
                        let items = [];
                        let itemText = field['item-text']|| 'text';
                        let itemValue = field['item-value']|| 'value';
                        for(let k in field.items){
                            items.push({
                                [itemValue]:k,
                                [itemText]:field.items[k]
                            });
                        }
                        field.attr['items'] = items;
                        field.attr['item-text'] = itemText;
                        field.attr['item-value'] = itemValue;

                    } else{
                        field.attr['items'] = field.items;
                    }
                }

                //-- Auto validation rules --//
                if(!field.attr.rules){
                    let rules = [];

                    if(field.attr.required){
                        rules.push( v=> !!v || trans('validation.required',{attribute:field.label||field.name}));
                    }
                    if(field.type === 'email'){
                        rules.push( v=> !!(!v || v.match(/[^@]+@[^@]+\.[\w]{2,}/)) || trans('validation.email',{attribute:field.label||field.name}));
                    }
                    /*
                    if(field.attr.min){
                        rules.push( v=> (!v || v.length>field.attr.min ) || trans('validation.min',{attribute:field.label||field.name}));
                    }
                    */

                    field.attr.rules = rules;
                }

                ///
                return field;
            });
        },
        componentList(){
            //Merge defined components with defaults
            return Object.assign({
                text:'v-text-field',
                textarea:'v-textarea',
                file:'v-file-input',
                select:'v-select',
            },this.components);
        },
        canSubmit(){
            return this.validForm;
        },
    },
    components:{
        FormHelperField,
    }
}
</script>
