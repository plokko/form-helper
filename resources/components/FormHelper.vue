<template>
    <form ref="form">
        <div v-if="error" class="error" ref="error">{{errorMessage}}</div>
        <slot name="items" v-bind="{items:fieldDataList}">
            <template v-for="field in fields">
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
    </form>
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
import FormHelperField from './FormHelperField';
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
                    field.errors = this.errors[name];
                }


                return field;
            });
        },


        fieldDataList(){
            let list = this.computedFields.map(field=>{
                let name = field.name;
                let self = this;

                return {
                    field:{
                        name,
                        type :field.type || 'text',
                        attr:field.attr,
                        component:field.component,

                        get visible(){
                            if(field.visible===undefined)
                                return true;
                            if(typeof field.visible ==='boolean')
                                return field.visible;
                            // Eval
                            return ((field,values)=> eval(this.visible))(field,self.item);
                        },

                        get loading(){return self.loading},

                        get value(){return self.item[name];},
                        set value(v){self.updateValue(name,v);},

                        get errors(){return self.errors && self.errors[name];},
                        set errors(v){if(val===null) self.clearErrors(name);},

                        clearErrors(){self.clearErrors(name);},
                        onFileChange(e){self.onFileChange(name,e);},
                    },
                }
            });

            list.byName = (name)=> list.find(e=> e.field.name==name);

            return list;
        },

        componentList(){
            //Merge defined components with defaults
            return Object.assign({
                text:'input',
                textarea:'textarea',
                select:'select',
            },this.components);
        },
        isEdit(){
            return !!this.item;
        },

        errorMessage(){
            return (this.error && this.error.response && this.error.response.data && this.error.response.data.message)
                || this.error;
        },
        canSubmit(){
            return this.$refs.form.checkValidity();
        },
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
        updateValue(name,value){
            this.item[name] = value;
            this.clearError(name);
        },
        clearError(name){
            this.error = null;
            if(this.errors && this.errors[name]){
                this.$delete(this.errors,name);
            }
        },
        onFileChange(name,e) {
            let files = e.target.files || e.dataTransfer.files;
            if(!e.target.multiple)
                files = files[0]||null;
            this.updateValue(name,files);
        },
    },
    watch:{
        item:{
            deep:true,
            handler(){
                this.$emit('input',this.item);
            }
        }
    },
    components:{
        FormHelperField,
    }
}
</script>
