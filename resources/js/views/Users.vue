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
                  <button
                    class="btn btn-sm p-0 px-1 mx-1"
                    :class="{'btn-primary': user.coupon_id==null,'btn-secondary': user.coupon_id!=null}"
                    @click="updateCoupon(user.id,index,0)"
                  >No Coupon</button>
                  <button
                    v-for="coupon in coupons"
                    :key="coupon.id"
                    class="btn btn-sm p-0 px-1 mx-1"
                    :class="{'btn-primary': user.coupon_id==coupon.id,'btn-secondary': user.coupon_id!=coupon.id}"
                    v-text="coupon.description"
                    @click="updateCoupon(user.id,index,coupon.id)"
                  ></button>
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
      users: {},
      coupons: {}
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
    updateCoupon(id, index, coupon_id) {
      axios
        .patch("/api/users/" + id + "/coupon", { coupon_id: coupon_id })
        .then(response => {
          this.users[index].coupon_id = response.data == "" ? null : response.data;
          window.flash("User's coupon has been updated", "success");
        });
    },
    ago(user) {
      return moment(user.created_at).fromNow();
    }
  },
  mounted() {
    axios.get("/api/users").then(response => (this.users = response.data));
    axios
      .get("/api/subscribe/coupons")
      .then(response => (this.coupons = response.data));
  }
};
</script>
