<template>
  <div class="row">
    <div class="col-sm-6 offset-sm-3 col-lg-12 offset-lg-0">
      <div class="form-group mb-1 text-center">
        <input
          id="new-dojo-img"
          class="form-control-file d-inline text-center"
          type="file"
          name="avatar"
          accept="image/*"
        />
      </div>
      <button id="uploadavatar" type="button" class="form-control btn btn-primary mb-1" @click="uploadImage">upload</button>
      <img id="dojo-image" :src="path" />
    </div>
  </div>
</template>

<script>
export default {
  props: ["currentimage", "dojo_id"],
  data() {
    return {
      path: ""
    };
  },
  watch: {
    // why tf is this necessary?
    currentimage: function(val) {
      this.path = val;
    }
  },
  methods: {
    uploadImage() {
      let input_elem = document.querySelector("#new-dojo-img");
      if (!input_elem.files.length) return;

      let avatar = input_elem.files[0];

      let data = new FormData();
      data.append("image", avatar);
      data.append("dojo", this.dojo_id);
      axios.post("/api/avatar", data).then(
        response => {
          this.path = response.data;
          window.flash("Your dojo's image has been updated!", "success");
        },
        error => {
          window.flash(
            "Looks like something went wrong :(. If this continues, please let the webmaster know",
            "danger"
          );
        }
      );
    }
  }
};
</script>

<style>
#dojo-image {
  max-width: 200px;
  display: block;
  margin: auto;
  width: 100%;
}
</style>