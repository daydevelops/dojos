<template>
  <div class="container">
    <div class="row">
      <div v-for="(user,index) in users" :key="user.id" class="col-sm-6">
        <div class="card text-left mb-2">
          <div class="card-body">
            <h4 class="card-title text-center">{{user.name}}</h4>
            <div class="card-text">
              <div class="row">
                <div class="col-sm-9">
                  <p class="m-0 p-0">Email: {{user.email}}</p>
                  <p class="m-0 p-0">Date Registered: {{ago(user)}}</p>
                  <p class="m-0 p-0">
                    <router-link :to="'/dojos/user/'+user.id">Dojos: {{user.dojos_count}}</router-link>
                  </p>
                </div>
                <div class="col-sm-3">
                  <button
                    class="btn d-block"
                    :class="{'btn-danger':user.is_active,'btn-success':!user.is_active}"
                    @click="toggleActive(user.id,index)"
                    v-text="user.is_active ? 'Deactivate' : 'Activate'"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  data() {
    return {
      users: {}
    };
  },
  methods: {
    toggleActive(id, index) {
      axios
        .patch("/api/users/" + id, { is_active: !this.users[index].is_active })
        .then(response => {
          this.users[index].is_active = response.data;
          window.flash(
            "User has been "+(response.data ? "" : "de") + "activated",
            "success"
          );
        });
    },
    ago(user) {
      return moment(user.created_at).fromNow();
    }
  },
  mounted() {
    axios.get("/api/users").then(response => (this.users = response.data));
  }
};
</script>
