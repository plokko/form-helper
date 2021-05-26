<template>
    <div>
        <v-form>
            <v-alert v-if="error" type="error" :value="true">
                <strong>{{errorMessage}}</strong>
            </v-alert>

            <template v-for="field in fieldList">
                <component
                    v-if="field.component"
                    :field="field"
                    :item="item"
                    v-model="item[field.name]"
                    v-bind="field.attr"
                    :error-messages
                    ></component>
                <slot
                    v-else
                    :name="'type.'+field.type"
                    >
                    <v-textarea
                        v-if="field.type==='textarea'"
                        :field="field"
                        v-model="item[field.name]"
                        v-bind="field.attr"
                        ></v-textarea>
                    <v-text-field
                        v-else
                        :type="field.type"
                        :field="field"
                        v-model="item[field.name]"
                        v-bind="field.attr"
                        ></v-text-field>
                </slot>
            </template>

            <slot name="submit" v-bind="{submit,canSubmit}">
                <v-btn @click="submit" :disabled="!canSubmit"><v-icon>send</v-icon></v-btn>
            </slot>
        </v-form>
    </div>
</template>
<script>
export default {
    name: "FormHelper",
    props: {
        action:{type:String,default:''},
        method:{type:String,default:'post'},
        fields:{array,required:true,},
        item:{Object,required:false,},
    },
    data() {
        return {

            loading:false,
            error:null,
            errors:null,
        };
    },
    computed: {

        fieldList(){
           return this.fields.map(f=>{
               if(!f.type)
                   f.type = 'text';
               if(f.rule){
                   try{
                       f.rule = eval(f.rule);
                   }catch(e){}
               }
               if(this.errors && this.errors[f.name])
                   f['error-messages'] = this.errors[f.name];
               return f
           })
        },
        isEdit(){
            return !!this.item;
        },

        errorMessage(){
            return (this.error && this.error.response && this.error.response.data && this.error.response.data.message)
                || this.error;
        },
    },
    methods: {

        submit(){

        },

    },
}
</script>
