<template>
  <div>
    <div
      class="modal fade"
      :id="id"
      tabindex="-1"
      role="dialog"
      aria-labelledby="modelTitleId"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="container-fluid">
              Are you sure you want to
              <span v-text="action"></span>?
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button
              type="button"
              class="btn"
              :class="'btn-'+btncolor"
              v-text="btntext"
              @click="$emit('confirm')"
              data-dismiss="modal"
            ></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["id", "action", "btncolor", "btntext"],
};
</script>
