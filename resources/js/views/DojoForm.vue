<template>
  <div>
    <div class="m-3 p-1 form-group row">
      <label class="col-sm-3 col-form-label text-center">Category:</label>
      <div class="col-sm-6 mb-2">
        <select class="form-control" name="category_id" v-model="form.category_id">
          <option v-for="cat in categories" :key="cat.id" v-bind:value="cat.id">{{cat.name}}</option>
        </select>
        <span
          class="help"
          v-if="form.errors.has('category_id')"
          v-text="form.errors.get('category_id')"
        ></span>
      </div>
      <div class="col col-sm-3 text-center">
        <router-link to="/categories/new">
          <button class="btn btn-primary">Add New Category</button>
        </router-link>
      </div>
    </div>
    <div class="card mb-4 p-3">
      <h5 v-if="showPremiumWarning" class="premium-required text-center" v-text="premiumWarningMessage"></h5>
      <form name='dojo-form' class="row" @submit.prevent="onSubmit" @keydown="form.errors.clear($event.target.name)">
        <!-- Avater Form -->
        <div v-if="is_editing" class="col-lg-3">
          <div class="form-group">
            <AvatarForm :currentimage="form.image" :dojo_id="this.dojo_id"></AvatarForm>
          </div>
        </div>

        <div class="col" :class="{'col-lg-9':is_editing}">
          <!-- Name -->
          <div class="row m-1 p-0 form-group">
            <label class="col-sm-3 col-form-label">Name:</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="name" v-model="form.name" />
              <span class="help" v-if="form.errors.has('name')" v-text="form.errors.get('name')"></span>
            </div>
          </div>

          <!-- Description -->
          <div class="row m-1 p-0 form-group">
            <label class="col-sm-3 col-form-label">Description:</label>
            <div class="col-sm-9">
              <textarea class="form-control" name="description" v-model="form.description"></textarea>
              <span
                class="help"
                v-if="form.errors.has('description')"
                v-text="form.errors.get('description')"
              ></span>
            </div>
          </div>

          <!-- Contact -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Contact Information:</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="contact" v-model="form.contact" />
              <span
                class="help"
                v-if="form.errors.has('contact')"
                v-text="form.errors.get('contact')"
              ></span>
            </div>
          </div>

          <!-- Price -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Price:</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="price" v-model="form.price" />
              <span class="help" v-if="form.errors.has('price')" v-text="form.errors.get('price')"></span>
            </div>
          </div>

          <!-- Classes -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Class Times:</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="classes" v-model="form.classes" />
              <span
                class="help"
                v-if="form.errors.has('classes')"
                v-text="form.errors.get('classes')"
              ></span>
            </div>
          </div>

          <!-- Website -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Website: <span v-if="showPremiumWarning" class="premium-required">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="website" v-model="form.website" placeholder="https://" />
              <span
                class="help"
                v-if="form.errors.has('website')"
                v-text="form.errors.get('website')"
              ></span>
            </div>
          </div>

          <!-- Facebook -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Facebook url: <span v-if="showPremiumWarning" class="premium-required">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="facebook" v-model="form.facebook" placeholder="https://" />
              <span
                class="help"
                v-if="form.errors.has('facebook')"
                v-text="form.errors.get('facebook')"
              ></span>
            </div>
          </div>

          <!-- Youtube -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Youtube url: <span v-if="showPremiumWarning" class="premium-required">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="youtube" v-model="form.youtube" placeholder="https://" />
              <span
                class="help"
                v-if="form.errors.has('youtube')"
                v-text="form.errors.get('youtube')"
              ></span>
            </div>
          </div>

          <!-- Twitter -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Twitter url: <span v-if="showPremiumWarning" class="premium-required">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="twitter" v-model="form.twitter" placeholder="https://" />
              <span
                class="help"
                v-if="form.errors.has('twitter')"
                v-text="form.errors.get('twitter')"
              ></span>
            </div>
          </div>

          <!-- Instagram -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">Instagram url: <span v-if="showPremiumWarning" class="premium-required">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="instagram" v-model="form.instagram" placeholder="https://" />
              <span
                class="help"
                v-if="form.errors.has('instagram')"
                v-text="form.errors.get('instagram')"
              ></span>
            </div>
          </div>

          <!-- Location -->
          <div class="m-1 p-0 form-group row">
            <label class="col-sm-3 col-form-label">
              Location: 
              <small v-if="showPremiumWarning" class="premium-required d-block">The map will not be shown for standard memberships, but you should still confirm your location in the map</small></label>
            <div class="col-sm-9">              
              <span
                class="help"
                v-if="form.errors.has('location')"
                v-text="form.errors.get('location')"
              ></span>
              <googlemap v-bind:start="form.location" @updateLocation="updateLocation"></googlemap>
            </div>
          </div>


          <!-- Buttons -->
          <div class="form-group row mt-4">
            <div class="col-12 text-right">
              <button
                id="updatedojo"
                type="submit"
                class="btn btn-primary m-auto btn-lg"
                v-bind:disabled="form.errors.any()"
              >Save</button>
              <button
                v-if="is_editing"
                type="button"
                class="btn btn-danger m-auto btn-sm"
                data-toggle="modal"
                data-target="#aysm"
              >Delete</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <paymentplans v-if="showPaymentPlans" :dojo_id="dojo_id"></paymentplans>
    <AreYouSureModal
      :id="'aysm'"
      action="delete this dojo"
      btncolor="danger"
      btntext="Delete"
      v-on:confirm="deleteDojo"
    ></AreYouSureModal>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: new Form({
        name: "What is your dojo's name?",
        description: "Provide a description of your dojo.",
        location: "Where are you located?",
        price: "How much do you charge for your services?",
        contact: "How can people contact you?",
        classes: "Describe your class schedule",
        category_id: 2, // none
        image: null
      }),
      is_editing: false, // are we editing an existing dojo. or creating a new one?
      dojo_id: 0,
      categories: {},
      location_selected: false,
      subscription_level: "",
    };
  },
  computed: {
    showPremiumWarning() {
      // show premium features if we are in the initial phase of the apps release, or the dojo has premium membership
      return window.App.app_phase > 0 && this.subscription_level !== 'premium';
    },
    premiumWarningMessage() {
      let messages = [
        '',
        'Some features (*) will be unavailable soon without a premium membership', // phase 1
        'Premium membership required for some features (*)' // phase 2
      ];
      return messages[window.App.app_phase];
    },
    showPaymentPlans() {
      return window.App.app_phase > 0 && this.is_editing;
    }
  },
  mounted() {
    axios.get("/api/categories").then(response => {
      this.categories = response.data;
    });

    // was an id given to this form?
    this.dojo_id = this.$route.params.id;
    if (this.dojo_id) {
      // if so, then we are editing a current dojo
      // get the data for that dojo
      axios.get("/api/dojos/" + this.dojo_id)
        .then(response => {
          this.form = new Form({
            name: response.data.name,
            description: response.data.description,
            location: JSON.parse(response.data.location),
            price: response.data.price,
            contact: response.data.contact,
            classes: response.data.classes,
            website: response.data.website,
            facebook: response.data.facebook,
            twitter: response.data.twitter,
            youtube: response.data.youtube,
            instagram: response.data.instagram,
            category_id: response.data.category_id,
            image: response.data.image
        });
        this.dojo_id = response.data.id;
        this.subscription_level = response.data.subscription_level;
        $('input.pac-target-input').val(this.form.location.formatted_address);
      })
      .catch(error => {
        if (error.response.status == 403) {
          this.$router.push("/");
        }
      });
      // this.dojo = this.$route.params.dojo;
      this.is_editing = true;
    } else {
      // we must be creating a new dojo
      this.is_editing = false;
    }
  },
  methods: {
    updateLocation(loc) {
      this.location_selected = true;
      this.form.location = loc;
    },
    onSubmit() {
      if (!this.location_selected) {
        alert('please select a valid location for the map');
        return false;
      }

      let action, path;
      if (this.is_editing) {
        action = "patch";
        path = "/api/dojos/" + this.dojo_id;
      } else {
        action = "post";
        path = "/api/dojos";
      }
      this.form
        .submit(action, path)
        .then(data => {
          if (!this.is_editing) {
            this.$router.push("/dojos/"+data[0].id);
          } else {
            window.flash("Your dojo has been updated!", "success");
          }
        })
        .catch(error => {});
    },
    deleteDojo() {
      axios.delete("/api/dojos/" + this.dojo_id).then(response => {
        this.$router.push("/");
      });
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
  onSuccess(data) {}
  onFail(error) {
    // debugger
    this.errors.record(error.response.data.errors);
  }
}
</script>
<style>
.help {
  color: red;
}
</style>