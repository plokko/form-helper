<template>
    <span>
        <slot
            name="item"
            v-bind="{field:fieldData,item}"
            >
            <component
                v-if="fieldData.type==='file'"
                :is="fieldData.component"
                v-bind="fieldData"

                @change="onFileChange"
                ></component>
            <component
                v-else
                :is="fieldData.component"
                v-bind="fieldData"

                v-model="model"
                ></component>
        </slot>
    </span>
</template>
<script>
export default {
    name: "FormHelperField",
    props: {
        field:{type:Object,required:true,},
        value:{required:false,},
        loading:{type:Boolean,default:false,},
        errors:{type:Array,default:null,},
        components:{type:Object,required:false,default(){return {};},},
    },
    data() {
        let self=this;
        return {
            data: this.value,
            item: {
                get value(){ return self.model; },
                set value(v){ self.model = v; },

                get loading(){ return self.loading; },
                get errors(){ return self.errors; },

                onFileChange(e){self.onFileChange(e);},
            },
        };
    },
    computed: {
        model:{
            get(){return this.data},
            set(v){
                this.data = v;
                this.$emit('input',this.data);
                this.clearError();
            }
        },
        name(){return this.field.name;},
        fieldData(){
            let f = this.field;
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
            if(this.errors){
                field['error-messages'] = this.errors;
            }

            if(!field.component ){
                //From compoenent map
                field.component = this.components[f.type] || this.components['text'] || 'input';
            }

            field.loading = this.loading;

            return field;
        }
    },
    methods: {
        clearError(){
            this.$emit('clear-error');
        },
        onFileChange(e) {
            let files = e.target.files || e.dataTransfer.files;
            if(!e.target.multiple)
                files = files[0]||null;
            this.model = files;
        },
    },
    watch:{
    }
}
</script>
