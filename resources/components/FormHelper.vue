<template>
    <div>
        <v-form ref="form" v-model="validForm">
            <v-expand-transition>
                <v-alert type="error" v-show="!!error">{{errorMessage}}</v-alert>
            </v-expand-transition>
            <template v-for="field in fieldList">
                <slot
                    :name="'field.'+field.name"
                    v-bind="{field,value:item[field.name],clearError:()=>{clearError(field.name)}}"
                    >
                    <slot
                        :name="'item.'+field.name"
                        v-bind="{field,value:item[field.name],clearError:()=>{clearError(field.name)}}"
                        >
                        <slot
                            :name="'type.'+field.type"
                            v-bind="{field,value:item[field.name],clearError:()=>{clearError(field.name)}}"
                            >

                            <component
                                v-if="field.type==='file'"
                                :is="field.component"
                                v-bind="field"

                                @change="v=>{item[field.name]=v;clearError(field.name);}"
                                ></component>
                            <component
                                v-else
                                :is="field.component"

                                v-model="item[field.name]"
                                v-bind="field"

                                @input="clearError(field.name)"
                                ></component>
                        </slot>
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
    </div>
</template>
<script>
function flattenObject(ob) {
    var res = {};

    for (var i in ob) {
        if (!ob.hasOwnProperty(i)) continue;

        if (!(ob[i] instanceof File) && (typeof ob[i]) == 'object' && ob[i] !== null) {
            var flatObject = flattenObject(ob[i]);
            for (var x in flatObject) {
                if (!flatObject.hasOwnProperty(x)) continue;

                res[i + '['+x+']' ] = flatObject[x];
            }
        } else {
            res[i] = ob[i];
        }
    }
    return res;
}
function objectToFormData(item){
    let data = flattenObject(item);

    let formData = new FormData();
    for(let key in data){
        let name = key.replace(/\.([^.]+)/g,'\[$1\]');
        let value = data[key];
        formData.append(name,value);
    }
    return formData;
}
export default {
    name: "FormHelper",
    props: {
        action:{type:String,default:''},
        method:{type:String,default:'post'},
        fields:{type:Array,required:true,},
        value:{type:Object,required:false,},

        submitText:{type:String,default:'Submit'},

        components:{type:Object,required:false,default(){return {};},},
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
            let components = Object.assign({
                    text:'v-text-field',
                    textarea:'v-textarea',
                    file:'v-file-input',
                    select:'v-select',
                },this.components);
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
               if(this.errors && this.errors[f.name]){
                   field['error-messages'] = this.errors[f.name];
               }

               if(!field.component ){
                   //From compoenent map
                   field.component = components[f.type] || components['text'];
               }

               field.loading = this.loading;
               return field
            });
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

            let data = objectToFormData(this.item||{});

            if(this.method!=='post'){
                data.append('_method',this.method);
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
        clearError(name){
            this.error = null;
            if(this.errors && this.errors[name]){
                this.$delete(this.errors,name);
            }
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
