<template>
  <div class="container">
    <div class="row mb-3">
      <div class="col col-md-9">
        <div class="form-group row">
          <label class="col-sm-2 text-center text-sm-left">Category:</label>
          <select class="form-control col-sm-10" name='category' @change="filterDojos()" v-model="selected_category">
            <option v-for="cat in categories" :key="cat.id" v-bind:value="cat.id">{{cat.name}}</option>
          </select>
        </div>
      </div>
      <div class="col-md-3 text-center text-md-right" v-if="signedIn">
        <div class="form-check">
          <label class="form-check-label">
            <input
              type="checkbox"
              name="show_my_dojos"
              class="form-check-input"
              value="0"
              v-model="filter_by_user"
              @change="filterDojos()"
            />
            Show My Dojos
          </label>
        </div>
      </div>
    </div>
    <div v-if="filtered_dojos.length > 0" class="row">
      <div class="col col-12" v-for="(dojo,index) in filtered_dojos" :key="dojo.id">
        <dojo v-bind:dojo="dojo" v-on:deleted="filtered_dojos.splice(index,1)" @showMap="showMap"></dojo>
      </div>
    </div>
    <div v-else class="row">
      <div class="col-12"><h4 class="text-center">There are no dojos here yet</h4></div>
    </div>

    <div class="modal fade" id="map-modal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" v-text="selected_dojo.name"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div v-if="this.selected_dojo.location" class="container-fluid">
              <gmap-map :center="selected_dojo.map_center" :zoom="11" style="width:100%;  height: 400px;">
                <gmap-marker :position="selected_dojo.map_center"></gmap-marker>
              </gmap-map>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      categories: {},
      selected_category: 1,
      filter_by_user: 0,
      user_id_filter: window.App.signedIn ? window.App.user.id : null,
      dojos: {},
      filtered_dojos: {},
      signedIn: window.App.signedIn,
      selected_dojo: {}
    };
  },
  mounted() {
    axios.get("/api/categories").then(response => {
      this.categories = response.data;
      this.selected_category = 1;
    });

    axios.get("/api/dojos").then(response => {
      this.dojos = response.data;
      // filter dojos by incoming user id
      if (this.$route.params.user_id) {
        this.user_id_filter = parseInt(this.$route.params.user_id);
        this.filter_by_user = 1;
      }
      this.filterDojos()
    });
  },
  methods: {
    toggleUserFilter() {
      if (this.filter_by_user) {
        this.filterByUser();
      } else {
        this.filtered_dojos = this.dojos;
        this.changeCategory();
      }
    },
    filterByUser() {
      if ((this.filter_by_user == true)) {
        this.filtered_dojos = this.filtered_dojos.filter(
          d => d.user_id == this.user_id_filter
        );
      }
    },
    filterByCategory() {
      if (this.selected_category == 1) {
        // do nothing
      } else {
        this.filtered_dojos = this.filtered_dojos.filter(
          d => d.category_id == this.selected_category
        );
      }
    },
    filterDojos() {
      this.filtered_dojos = this.dojos;
      this.filterByCategory();
      this.filterByUser();
    },
    showMap(dojo) {
      this.selected_dojo = dojo;
      var loc = JSON.parse(this.selected_dojo.location);
      this.selected_dojo.map_center = loc.geometry.location;
      $('#map-modal').modal('show');
    }
  }
};
</script>