<template>
  <div class="container">
    <form class="row" @submit.prevent="onSubmit" @keydown="form.errors.clear($event.target.name)">
      <div class="col col-sm-6 offset-sm-3">
        <input
          type="text"
          class="form-control"
          name="name"
          placeholder="Name..."
          v-model="form.name"
        />
        <span class="help" v-if="form.errors.has('name')" v-text="form.errors.get('name')"></span>
      </div>
      <div class="col col-sm-3">
        <button type="submit" class="btn btn-primary">Add</button>
      </div>
    </form>
    <h2 class=" col-sm-6 offset-sm-3 text-center">Available Categories:</h2>
    <div class="row">
      <div class="col-sm-6 offset-sm-3">
        <ul class="p-0">
          <li
            v-for="(cat,index) in categories"
            :key="cat.id"
            class="mb-1 alert alert-primary d-flex justify-content-between"
            style="list-style-type:none;"
          >
            <span v-text="cat.name"></span>
            <button
              v-if="cat.id > 2"
              class="btn btn-danger btn-sm mr-2"
              @click="deleteCategory(cat.id,index)"
            >x</button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      categories: {},
      form: new Form({
        name: ""
      })
    };
  },
  mounted() {
    axios.get("/api/categories").then(response => {
      this.categories = response.data;
    });
  },
  methods: {
    deleteCategory(id, index) {
      axios
        .delete("/api/categories/" + id)
        .then(response => {
          this.categories.splice(index, 1);
          window.flash(
            "Category has been deleted",
            "success"
          );
        })
        .catch(errors => {
          console.log(errors);
        });
    },
    onSubmit() {
      this.form
        .submit("post", "/api/categories")
        .then(data => {
          this.categories.push(data);
          window.flash(
            "Category has been added",
            "success"
          );
        })
        .catch(error => console.log(error));
    }
  }
};

class Errors {
  constructor() {
    this.errors = {};
  }
  get(field) {
    return this.errors[field] ? this.errors[field][0] : "";
  }
  record(error) {
    this.errors = error;
  }
  clear(field) {
    delete this.errors[field];
  }
  has(field) {
    return !!this.errors[field];
  }
  any() {
    return Object.keys(this.errors).length > 0;
  }
}

class Form {
  constructor(fields) {
    this.original_data = fields;
    for (let f in fields) {
      this[f] = fields[f];
    }
    this.errors = new Errors();
  }
  reset(fields) {
    for (let f in fields) {
      this[f] = "";
    }
  }
  data() {
    let data = {};
    for (let property in this.original_data) {
      data[property] = this[property];
    }
    return data;
  }
  submit(method, url) {
    return new Promise((resolve, reject) => {
      axios[method](url, this.data())
        .then(response => {
          this.onSuccess(response.data);
          resolve(response.data);
        })
        .catch(error => {
          this.onFail(error);
          reject(error);
        });
    });
  }
  onSuccess(data) {
    this.reset(this.original_data);
  }
  onFail(error) {
    // debugger
    this.errors.record(error.response.data.errors);
  }
}
</script>
