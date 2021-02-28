<template>
  <div>
    <div>
      <label>
        <gmap-autocomplete @place_changed="setPlace"></gmap-autocomplete>
        <button type="button" class="pac-button btn btn-primary w-20 mr-0" @click="updateMarker">Update Location</button>
      </label>
      <br />
    </div>
    <br />
    <gmap-map :center="center" :zoom="12" style="width:100%;  height: 400px;">
      <gmap-marker :position="marker" @click="center=marker"></gmap-marker>
    </gmap-map>
  </div>
</template>

<script>
export default {
  name: "GoogleMap",
  props: ["start"],
  data() {
    return {
      // default to Montreal to keep it simple
      // change this to whatever makes sense
      center: { lat: 45.508, lng: -73.587 },
      marker: { lat: 45.508, lng: -73.587 },
      places: [],
      currentPlace: null
    };
  },
  watch: {
    start(new_val) {
      this.setPlace(this.start);
      this.updateMarker();
    }
  },
  mounted() {
    setTimeout(function() {
        $('input.pac-target-input').addClass('form-control');
        $('input.pac-target-input').parent().addClass('form-group');
        $('input.pac-target-input').parent().addClass('w-100');
    },3000);
  },

  methods: {
    // receives a place object via the autocomplete component
    setPlace(place) {
      this.currentPlace = place;
    },
    updateMarker() {
      if (this.currentPlace) {
        // if currentPlace comes from the input, lat and lng are functions,
        // if currentPlace comes from database, they are numbers
        // I hate myself for this
        var location = this.currentPlace.geometry.location;
        if (typeof location.lat === "function") {
          var lat = location.lat();
          var lng = location.lng();
        } else {
          var lat = location.lat;
          var lng = location.lng;
        }

        this.marker = {
          lat: lat,
          lng: lng
        };
        this.center = this.marker;
        this.$emit("updateLocation", this.currentPlace);
      }
    }
  }
};
</script>