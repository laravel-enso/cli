<template>

    <vue-table class="box"
        :path="path"
        id="${models}"/>

</template>

<script>

import VueTable from '../../../components/enso/vuedatatable/VueTable.vue';

export default {
    components: { VueTable },

    data() {
        return {
            path: route('${initTable}'),
        };
    },
};

</script>

<style lang="scss">
</style>