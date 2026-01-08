<template>
    <div>
        <h1>Home Component</h1>
        <p>{{ message }}</p>
    </div>
</template>

<script>

export default {
    name: 'HomeComponent',
    data() {
        return {
            message: 'Hello World from Vue',
        }
    },
    mounted() {
        // console.log(this.message);
        window.Echo.channel('Notification').listen('NotificationReceived', (e) => {
            console.log(e.notification);
            this.message = e.notification;
        });
    }
}
</script>

