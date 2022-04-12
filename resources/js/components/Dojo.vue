<template>
  <div>
    <div class="card mb-4 p-3" :class="{'premium-dojo':dojo.subscription_level=='premium'}">
      <div class="row">
        <div class="col-lg-3">
          <div>
            <img class="dojo-img mb-2" v-bind:src="dojo.image" />
          </div>
          <div v-if="showPremiumFeatures" class="social-media-icons text-center">
            <small>
            <a v-if="dojo.website"   :href="dojo.website" target="_blank"><i class="social-media-icon fas fa-2x fa-globe"></i></a>
            <a v-if="dojo.facebook"  :href="dojo.facebook" target="_blank"><i class="social-media-icon fab fa-2x fa-facebook"></i></a>
            <a v-if="dojo.twitter"   :href="dojo.twitter" target="_blank"><i class="social-media-icon fab fa-2x fa-twitter"></i></a>
            <a v-if="dojo.instagram" :href="dojo.instagram" target="_blank"><i class="social-media-icon fab fa-2x fa-instagram"></i></a>
            <a v-if="dojo.youtube"   :href="dojo.youtube" target="_blank"><i class="social-media-icon fab fa-2x fa-youtube"></i></a>
            </small>
          </div>
          <div v-if="canSeeViews" class="text-center mt-2">
            <i class="fa fa-eye" aria-hidden="true"></i> <span v-text="' ' + dojo.views"></span>
          </div>
        </div>
        <div class="col-lg-9">
          <div class="row">
            <div class="col-sm-8">
              <i><small v-text="dojo.category.name"></small></i>
              <p class='text-danger' v-if="!dojo.is_active">Hidden: Owner is deactivated</p>
              <p class='text-danger' v-if="dojo.subscription_level=='free' && canEdit && phase2">Hidden: subscribe this dojo to show it publicly</p>
              <h4 class="card-title">{{dojo.name}}</h4>
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
          <div class="row">
            <div class="col col-12">
              <p>{{dojo.description}}</p>
            </div>
          </div>
          
          <div class="row">
            <div class="col-12 col-md-2">
              <p class="mb-0"><strong>Location:</strong></p>
            </div>
            <div class="col-12 col-md-10">
              <p class="mb-1">
                <span v-if="JSON.parse(dojo.location).geometry">
                {{JSON.parse(dojo.location).formatted_address}}
                <button v-if="showPremiumFeatures" class='btn btn-primary btn-sm' @click="show_map = 1">Show map?</button>
                </span>
              </p>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-2">
              <p class="mb-0"><strong>Contact:</strong></p>
            </div>
            <div class="col-12 col-md-10">
              <p class="mb-1">{{dojo.contact}}</p>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-2">
              <p class="mb-0"><strong>Price:</strong></p>
            </div>
            <div class="col-12 col-md-10">
              <p class="mb-1">{{dojo.price}}</p>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-2">
              <p class="mb-0"><strong>Schedule:</strong></p>
            </div>
            <div class="col-12 col-md-10">
              <p class="mb-1">{{dojo.classes}}</p>
            </div>
          </div>
        </div>
      </div>
      <div v-if="JSON.parse(this.dojo.location).geometry && showPremiumFeatures && show_map" class="row">
        <div class="col-12">
          <gmap-map :center="JSON.parse(this.dojo.location).geometry.location" :zoom="11" style="width:100%;  height: 200px;">
            <gmap-marker :position="JSON.parse(this.dojo.location).geometry.location"></gmap-marker>
          </gmap-map>
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
  data() {
    return {
      show_map: 0,
    }
  },
  computed: {
    canEdit() {
      return this.isOwner;
    },
    canSeeViews() {
      return this.isOwner;
    },
    isOwner() {
      if (window.App.user != null) {
        return (
          window.App.user.is_admin || window.App.user.id == this.dojo.user_id
        )
      } else {
        return false;
      }
    },
    phase2() {
      return window.App.app_phase == 2;
    },
    showPremiumFeatures() {
      // show premium features if we are in the initial phase of the apps release, or the dojo has premium membership
      return window.App.app_phase < 2 || this.dojo.subscription_level == 'premium';
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
    },
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