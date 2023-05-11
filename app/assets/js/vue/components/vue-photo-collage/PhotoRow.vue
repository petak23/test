<script>
/** 
 * @lastchange 02.03.2023
 *
 * @editedby Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @version 1.0.2
 */
export default {
  props: {
    rowHeight: Number,
    rowPadding: Number,
  },
};
</script>

<template>
  <div class="vue-photo-row" :style="{ height: rowHeight+'px', 'padding-bottom': rowPadding+'px' }">
    <slot></slot>
  </div>
</template>

<style lang="scss" scoped>
.vue-photo-row {

  display: flex;
  box-sizing: border-box;

  & + & {
    margin-top: var(--vue-photo-grid-gap);
  }

}
</style>
