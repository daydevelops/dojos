<template>
  <div class="container">
    <div class="row">
      <div class="col col-md-8 offset-md-2 col-12">
        <div class="form-group">
          <label for>Category</label>
          <select class="form-control" @change="changeCategory()" v-model="selected_category">
            <option v-for="cat in categories" :key="cat.id" v-bind:value="cat.id">{{cat.name}}</option>
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col col-12" v-for="(dojo,index) in filtered_dojos" :key="dojo.id">
       <dojo v-bind:dojo="dojo" v-on:deleted="filtered_dojos.splice(index,1)"></dojo>
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
      dojos: {},
      filtered_dojos: {}
    };
  },
  mounted() {
    axios.get("/api/categories").then(response => {
      this.categories = response.data;
      this.selected_category = 1;
    });

    axios.get("/api/dojos").then(response => {
      this.dojos = response.data;
      this.filtered_dojos = response.data;
    });
  },
  methods: {
    changeCategory() {
      if (this.selected_category == 1) {
        this.filtered_dojos = this.dojos; // all
      } else {
        this.filtered_dojos = this.dojos.filter(
          d => d.category_id == this.selected_category
        );
      }
    }
  }
};
</script>