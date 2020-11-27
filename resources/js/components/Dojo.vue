<template>
  <div>
    <div class="card mb-4 p-3">
      <div class="row">
        <div class="col-lg-3">
          <img class="dojo-img mb-2" v-bind:src="dojo.image" />
        </div>
        <div class="col-lg-9">
          <div class="row">
            <div class="col-sm-8">
              <i><small v-text="dojo.category.name"></small></i>
              <h4 class="card-title">{{dojo.name}} <small class='text-danger' v-if="!dojo.is_active">[Hidden due to owner deactivation]</small></h4>
            </div>
            <div v-if="canEdit" class="col-sm-4 text-sm-right">
              <i
                class="fas fa-2x fa-trash-alt m-2 text-danger"
                data-toggle="modal"
                :data-target="'#modal-dojo-'+this.dojo.id"
              ></i>
              <router-link :to="{name:'EditDojo',params:{id:this.dojo.id}}">
                <i class="fas fa-2x fa-edit m-2 text-success"></i>
              </router-link>
            </div>
          </div>
          <p class="m-0 p-0">{{dojo.description}}</p>
          <p class="m-0 p-0">
            <u class="mr-3">Location:</u>
            {{dojo.location}}
          </p>
          <p class="m-0 p-0">
            <u class="mr-3">Contact Information:</u>
            {{dojo.contact}}
          </p>
          <p class="m-0 p-0">
            <u class="mr-3">Price:</u>
            {{dojo.price}}
          </p>
          <p class="m-0 p-0">
            <u class="mr-3">Class Times:</u>
            {{dojo.classes}}
          </p>
        </div>
      </div>
    </div>
    <AreYouSureModal
      :id="'modal-dojo-'+dojo.id"
      action="delete this dojo"
      btncolor="danger"
      btntext="Delete"
      v-on:confirm="deleteDojo"
    ></AreYouSureModal>
  </div>
</template>

<script>
export default {
  props: ["dojo"],
  computed: {
    canEdit() {
      if (window.App.user != null) {
        return (
          window.App.user.is_admin || window.App.user.id == this.dojo.user_id
        )
      } else {
        return false;
      }
    }
  },
  methods: {
    deleteDojo() {
      axios.delete("/api/dojos/" + this.dojo.id)
      .then(response => {
        this.$emit('deleted');
          window.flash(
            "Dojo has been deleted",
            "success"
          );
      })
    }
  }
};
</script>

<style>
.dojo-img {
  max-width: 200px;
  display: block;
  margin: auto;
  width: 100%;
}
.card {
  box-shadow: 0px 0px 26px -18px black;
}
</style>