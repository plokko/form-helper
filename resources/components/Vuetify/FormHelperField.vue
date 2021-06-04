<template>
    <span v-if="field.visible">
        <slot
            name="item"
            v-bind="{field}"
            >
            <component
                v-if="field.type==='file'"
                :is="field.component"
                v-bind="field.attr"

                @change="field.onFileChange"
            ></component>
            <component
                v-else
                :is="field.component"
                v-bind="field.attr"

                v-model="field.value"
            ></component>
        </slot>
    </span>
</template>
<script>
export default {
    name: "VuetifyFormHelperField",
    props: {
        field:{type:Object,required:true,},
    },
}
</script>
