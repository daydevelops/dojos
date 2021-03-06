<template>
  <div class="container">
    <div class="row">
      <div v-for="(user,index) in users" :key="user.id" class="col-lg-6">
        <div class="card text-left p-2 mb-2">
          <div class="card-body p-1">
            <h4 class="card-title text-sm-center">{{user.name}}</h4>
            <div class="card-text">
              <div class="row">
                <div class="col-sm-9">
                  <p class="m-0 p-0">Email: {{user.email}}</p>
                  <p class="m-0 p-0">Date Registered: {{ago(user)}}</p>
                  <div class="form-group mb-0">
                    <label class="d-inline">Discount (% taken off)</label>
                    <input type="number" min="0" max="100" v-model="user.discount" class="form-control w-25 py-0 ml-2 d-inline" />
                  </div>
                  <p class="m-0 p-0">
                    <router-link :to="'/dojos/user/'+user.id">Dojos: {{user.dojos_count}}</router-link>
                  </p>
                </div>
                <div class="col-sm-3">
                  <button
                    class="btn btn-sm d-block"
                    :class="{'btn-danger':user.is_active,'btn-success':!user.is_active}"
                    @click="toggleActive(user.id,index)"
                    v-text="user.is_active ? 'Deactivate' : 'Activate'"
                  ></button>
                  <button
                    class="btn btn-sm d-block btn-primary"
                    @click="updateDiscount(user.id,index)"
                  >Update</button>

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
            "User has been " + (response.data ? "" : "de") + "activated",
            "success"
          );
        });
    },
    updateDiscount(id,index) {
      axios
        .patch("/api/users/" + id + "/discount", { discount: this.users[index].discount })
        .then(response => {
          this.users[index].discount = response.data;
          window.flash(
            "User's discount has been updated to " + response.data,
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
