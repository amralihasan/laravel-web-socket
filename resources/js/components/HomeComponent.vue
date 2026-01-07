<template>
    <div>
        <h1>Home Component</h1>
    </div>
</template>

<script>
export default {
    name: 'HomeComponent',
    data() {
        return {
            message: 'Hello World',
        }
    },
    mounted() {
        console.log(this.message);
    }
}
</script>

