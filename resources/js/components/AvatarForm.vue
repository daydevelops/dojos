<template>
  <div>
    <div class="form-group">
      <input id="new-dojo-img" class="form-control" type="file" name="avatar" accept="image/*" />
    </div>
      <button type="button" class="form-control btn btn-primary" @click="uploadImage">upload</button>
    <img id="dojo-image" :src="path" />
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
        },
        error => {
          console.log(error);
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