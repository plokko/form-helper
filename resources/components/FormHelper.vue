<template>
    <div>
        <v-form ref="form" v-model="validForm">
            <v-alert v-if="error" type="error" :value="true">
                <strong>{{errorMessage}}</strong>
            </v-alert>

            <template v-for="field in fieldList">
                <slot
                    :name="'field.'+field.name"
                    v-bind="{field,value:item[field.name],}"
                    >
                    <component
                        v-if="field.component"
                        :field="field"
                        :item="item"
                        v-model="item[field.name]"
                        v-bind="field"
                        ></component>
                    <slot
                        v-else
                        :name="'type.'+field.type"
                        v-bind="{field,value:item[field.name],}"
                        >
                        <v-textarea
                            v-if="field.type==='textarea'"
                            v-model="item[field.name]"
                            v-bind="field"
                            ></v-textarea>
                        <v-text-field
                            v-else
                            v-model="item[field.name]"
                            v-bind="field"
                            ></v-text-field>
                    </slot>
                </slot>
            </template>

            <slot name="submit" v-bind="{submit,canSubmit,loading}">
                <v-btn
                    @click="submit"
                    :disabled="!canSubmit"
                    :loading="loading"
                    color="primary"
                    ><v-icon left>send</v-icon> {{submitText}}</v-btn>
            </slot>
        </v-form>
        <pre>{{item}}</pre>
    </div>
</template>
<script>
export default {
    name: "FormHelper",
    props: {
        action:{type:String,default:''},
        method:{type:String,default:'post'},
        fields:{type:Array,required:true,},
        value:{type:Object,required:false,},

        submitText:{type:String,default:'Submit'},
    },
    data() {
        let item = Object.assign(Object.fromEntries(this.fields.map(f=>[f.name,null])),this.value||{});

        return {
            item,

            validForm:false,

            loading:false,
            error:null,
            errors:null,
        };
    },
    computed: {

        fieldList(){
           return this.fields.map(f=>{
               let field = {type:'text',};

               for(let k in f){
                   if(k[0]===':'){
                       try{
                           field[k.substr(1)] = eval(f[k]);
                       }catch(e){
                           console.error('field "'+k+'" evaluation error:',e);
                       }
                   }else{
                       field[k]=f[k];
                   }
               }
               if(this.errors && this.errors[f.name])
                   field['error-messages'] = this.errors[f.name];

               field.loading=this.loading;
               return field
           })
        },
        isEdit(){
            return !!this.item;
        },

        errorMessage(){
            return (this.error && this.error.response && this.error.response.data && this.error.response.data.message)
                || this.error;
        },
        canSubmit(){
            return this.validForm;
        }
    },
    methods: {

        submit(){
            this.loading = true;
            this.error = this.errors = null;

            let data = Object.assign({},data);
            if(this.method!=='post'){
                data._method = this.method;
            }

            axios.post(this.action,data)
                .then(r=>{
                    this.$emit('submit',r);
                })
                .catch(e=>{
                    this.error = (e && e.response && e.response.data &&  e.response.data.message) || e;
                    this.errors = e && e.response && e.response.data &&  e.response.data.errors;
                    this.$emit('error',e);
                })
                .finally(()=>{this.loading=false;})
                ;
        },

    },
    watch:{
        item:{
            deep:true,
            handler(){
                this.$emit('value',this.item);
            }
        }
    }
}
</script>
