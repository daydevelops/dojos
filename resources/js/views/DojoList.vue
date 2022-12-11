<template>
  <div class="container">
    <div class="row mb-3">
      <div class="col col-md-9">
        <div class="form-group row">
          <label class="col-md-3 text-center text-md-left"><h3>Category:</h3></label>
          <select
            class="form-control col-md-4"
            name="category"
            @change="filterDojos()"
            v-model="selected_category"
          >
            <option
              v-for="cat in categories"
              :key="cat.id"
              v-bind:value="cat.id"
            >
              {{ cat.name }}
            </option>
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
      <!-- Show Premium dojos first -->
      <div
        class="col col-12"
        v-for="dojo in shownDojos"
        :key="'premium' + dojo.id"
      >
        <dojo
          v-observe-visibility="{
            callback: visibilityChanged,
            once: true,
          }"
          v-if="dojo.subscription_level == 'premium'"
          v-bind:dojo="dojo"
          v-on:deleted="deleteDojo(dojo.id)"
          :id="'dojo-'+dojo.id"
        ></dojo>
      </div>
      <!-- Show standard dojos second -->
      <div
        class="col col-12"
        v-for="dojo in shownDojos"
        :key="'standard' + dojo.id"
      >
        <dojo
          v-observe-visibility="{
            callback: visibilityChanged,
            once: true,
          }"
          v-if="dojo.subscription_level == 'standard'"
          v-bind:dojo="dojo"
          v-on:deleted="deleteDojo(dojo.id)"
          :id="'dojo-'+dojo.id"
        ></dojo>
      </div>
      <!-- show free dojos last -->
      <div
        class="col col-12"
        v-for="dojo in shownDojos"
        :key="'free' + dojo.id"
      >
        <dojo
          v-observe-visibility="{
            callback: visibilityChanged,
            once: true,
          }"
          v-if="dojo.subscription_level == 'free'"
          v-bind:dojo="dojo"
          v-on:deleted="deleteDojo(dojo.id)"
          :id="'dojo-'+dojo.id"
        ></dojo>
      </div>
    </div>
    <div v-else class="row">
      <div class="col-12">
        <h4 class="text-center">There are no dojos here yet</h4>
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
    };
  },
  mounted() {
    axios.get("/api/categories").then((response) => {
      this.categories = response.data;
      this.selected_category = 1;
    });

    axios.get("/api/dojos").then((response) => {
      this.dojos = response.data;
      // filter dojos by incoming user id
      if (this.$route.params.user_id) {
        this.user_id_filter = parseInt(this.$route.params.user_id);
        this.filter_by_user = 1;
      }
      this.filterDojos();
    });
  },
  computed: {
    shownDojos() {
      let dojos = this.filtered_dojos;
      for (let i = dojos.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [dojos[i], dojos[j]] = [dojos[j], dojos[i]];
      }
      return dojos;
    }
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
      if (this.filter_by_user == true) {
        this.filtered_dojos = this.filtered_dojos.filter(
          (d) => d.user_id == this.user_id_filter
        );
      }
    },
    filterByCategory() {
      if (this.selected_category == 1) {
        // do nothing
      } else {
        this.filtered_dojos = this.filtered_dojos.filter(
          (d) => d.categories.map((c) => c.id).indexOf(this.selected_category) != -1
        );
      }
    },
    filterDojos() {
      this.filtered_dojos = this.dojos;
      this.filterByCategory();
      this.filterByUser();
    },
    deleteDojo(id) {
      this.filtered_dojos = this.filtered_dojos.filter(
        (d) => d.id != id
      );
      this.dojos = this.dojos.filter(
        (d) => d.id != id
      );
    },
    visibilityChanged(isVisible, entry) {
      if (entry.isVisible || entry.isIntersecting) {
        let dojo_id = entry.target.id.substring(5);
        axios.post("/api/dojos/view/" + dojo_id);
      }
    },
  },
};
</script>