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

                res[i + '.'+x] = flatObject[x];
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
        console.log(name,value)
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
                        type: field.type || 'text',
                        attr: field.attr,
                        component: field.component || 'text',

                        items: field.items,
                        'item-text': field['item-text'],
                        'item-value': field['item-value'],
                        /*
                        get itemsValues(){
                            return field && field.items &&  (Array.isArray(fields.items)? field.items:Object.values(field.items)).map( e=> (field['item-value'])?e[field['item-value']]:e );
                        },*/

                        get visible(){return self.computeFieldVisibility(field)},

                        get loading(){return self.loading},

                        get value(){return self.item[name];},
                        set value(v){
                            if(field.type=='file')
                                self.onFileChange(name,v);
                            else
                                self.updateValue(name,v);
                        },

                        get errors(){
                            return self.flatternErrors[name];
                        },
                        get allErrors(){return self.errors;},
                        set errors(v){if(val===null) self.clearErrors(name);},

                        clearErrors(){self.clearErrors(name);},
                        onFileChange(e){self.onFileChange(name,e);},

                        search(search){return self.searchQuery(name,search);},
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
        flatternErrors(){
            let errors = {};
            if(this.errors){
                for(let k in this.errors){
                    let baseName = (k.match(/^[^.]+/))[0];
                    errors[baseName] = this.errors[k];
                }
            }
            return errors;
        },
    },
    methods: {

        submit(){
            this.loading = true;
            this.error = this.errors = null;

            let data = Object.assign({},this.item||{});
            //Remove null files from data to avoid validation conflicts
            for(let field of this.fields){
                if(field.type==='file' && data[field.name]==null){
                    delete data[field.name];
                }
            }
            console.log({data,original:this.item})
            //Convert to FormData for file uploads
            let formData = objectToFormData(data);
            //Add method
            if(this.method!=='post'){
                formData.append('_method',this.method);
            }

            axios.post(this.action,formData)
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
        computeFieldVisibility(field){
            if(field.visible===undefined || field.visible===null)
                return true;
            if((typeof field.visible) ==='boolean')
                return field.visible;

            //Eval
            return ((field, values)=> eval(field.visible))(field,this.item);
        },
        updateValue(name,value){
            this.clearError(name);
            this.item[name] = value;
        },
        clearError(name){
            this.error = null;
            if(this.errors && this.errors[name]){
                delete this.errors[name];
                this.errors = Object.assign({},this.errors);
            }
        },



        /**
         * Execute search for QuerableFormFields
         * @param {string} name Field name
         * @param {event}
         */
        onFileChange(name,e){
            if(e instanceof Event){
                let files = e.target.files || e.dataTransfer.files;
                if(!e.target.multiple)
                    files = files[0]||null;
                this.updateValue(name,files);
            }else{
                this.updateValue(name,e);
            }
        },

        /**
         * Execute search for QuerableFormFields
         * @param {string} name Field name
         * @param {string|mixed} search Search string
         * @returns {Promise<AxiosResponse<any>>}
         */
        searchQuery(name,search){
            return axios.get('',{
                headers:{
                    'X-FORMHELPER-FIELDQUERY-FIELD':name,
                    'X-FORMHELPER-FIELDQUERY-SEARCH':search,
                }
            });
        }
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
