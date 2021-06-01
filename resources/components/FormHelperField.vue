<template>
    <div>
        <slot
            name="item"
            v-bind="{fieldData,value:data,clearError:()=>{clearError()}}"
            >
            <component
                v-if="fieldData.type==='file'"
                :is="fieldData.component"
                v-bind="fieldData"

                @change="v=>{data=v;clearError();}"
                ></component>
            <component
                v-else
                :is="fieldData.component"

                v-model="data"
                v-bind="fieldData"

                ></component>
        </slot>
        ITEMVAL: {{data}}
    </div>
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
        return {
            data: this.value,
        };
    },
    computed: {
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
            this.$emit('clearError');
        },
    },
    watch:{
        data:{
            deep:true,
            handler(){
                this.$emit('input',this.data);
                this.clearError();
            }
        }
    }
}
</script>
