<script>
/** 
 * @lastchange 30.03.2022
 *
 * @editedby Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @version 1.0.1
 */
export default {
  props: {
    photostyle: Object,
  },
  computed: {
    thumbStyle() {
      let style = {};
      if (typeof this.photostyle.width !== 'undefined') {
        style.width = this.photostyle.width + '%';
      }
      return style;
    }
  }
}
</script>

<template>
  <div 
    class="vue-photo-grid" 
    @click="$emit('click')"
    :style="thumbStyle"
  >

    <slot></slot>
  </div>
</template>

<style lang="scss" scoped>
.vue-photo-grid {
  display: flex;
  position: relative;
  flex: auto; /*1;*/
  cursor: pointer;
  
  & + & {
    margin-left: var(--vue-photo-grid-gap);
  }

}
</style>
