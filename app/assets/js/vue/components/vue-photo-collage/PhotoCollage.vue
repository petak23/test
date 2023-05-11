<template>
  <div
    class="vue-photo-collage"
    :class="[disabled && 'vue-photo-collage--disabled']"
    :style="photoCollageStyle">
    <row-photos
      @itemClick="(data, i) => !disabled && $emit('itemClick', data, i)"
      v-for="(data, i) in layout"
      :key="i"
      :height="height[i]"
      :padding="padding[i]"
      :photos="layoutPhotoMaps[i]"
      :layoutNum="layoutNum"
      :remainingNum="remainingNum"
      :showNumOfRemainingPhotos="showNumOfRemainingPhotos"
      :maxRandomPercWidth="maxRandomPercWidth"
      :widerPhotoId="widerPhotoId[i]"
    ></row-photos>
  </div>
</template>

<script>
import RowPhotos from "./RowPhotos.vue";

export default {
  components: {
    RowPhotos,
  },
  props: {
    disabled: {
      type: Boolean,
      default: false,
    },
    width: String,
    height: Array,
    padding: Array,
    layout: Array,
    layoutPhotoMaps: Object,
    layoutNum: Number,
    remainingNum: Number,
    showNumOfRemainingPhotos: Boolean,
    maxRandomPercWidth: Number,
    widerPhotoId: Array,
  },
  computed: {
    photoCollageStyle() {
      return {
        width: this.width,
      };
    },
  },
};
</script>

<style lang="scss">
.vue-photo-collage {
  font-family: inherit;
}

.vue-photo-collage.vue-photo-collage--disabled {
  .vue-photo-grid, .vue-view-more {
    cursor: inherit;
  }
}
</style>
