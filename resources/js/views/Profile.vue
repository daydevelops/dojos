<template>
  <div class="container">
        <p v-text="user.name"></p>
        <p v-text="user.email"></p>
  </div>
</template>

<script>
import moment from "moment";

export default {
  data() {
    return {
      user: {}
    };
  },
  mounted() {
    axios
      .get("/api/users/" + this.$route.params.user_id)
      .then(response => (this.user = response.data))
      .catch(error => {
        if (error.response.status == 403) {
          this.$router.push("/");
        }
      });;
  }
};
</script>
