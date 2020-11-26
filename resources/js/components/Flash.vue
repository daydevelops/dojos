/* HOW TO USE THIS COMPONENT

    Add this to your app.js or bootstrap.js file so it is set globally:
        window.events = new Vue();
        window.flash = function( message, level ) {
            window.events.$emit('flash', {message,level});
        };
    Add the component to your main Vue app:
        <flash></flash>
    Create the flash message anywhere by calling:
        window.flash("my message","bootstrap-color")

*/ 

<template>
  <div class="alert alert-flash border border-dark" :class="'alert-'+color" role="alert" v-show="show">{{ body }}</div>
</template>

<script>
export default {
  props: ["message", "level"],
  data() {
    return {
      body: "",
      color: "",
      show: false,
      timer: null
    };
  },
  created() {
    window.events.$on("flash", data => this.flash(data));
  },
  methods: {
    flash(data) {
      this.body = data.message;
      this.color = data.level;
      this.show = true;
      clearTimeout(this.timer);
      this.hide();
    },
    hide() {
      this.timer = setTimeout(() => {
        this.show = false;
      }, 3000);
    }
  }
};
</script>

<style>
.alert-flash {
  position: fixed;
  right: 25px;
  bottom: 25px;
}
</style>