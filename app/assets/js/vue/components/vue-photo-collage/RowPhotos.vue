<script>
/** 
 * @lastchange 02.03.2023
 *
 * @editedby Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @version 1.0.2
 */
import PhotoGrid from "./PhotoGrid.vue"
import PhotoRow from "./PhotoRow.vue"
import PhotoMask from "./PhotoMask.vue"
import ViewMore from "./ViewMore.vue"
import NumOfRemaining from "./NumOfRemaining.vue"
import PhotoThumb from './PhotoThumb.vue'

export default {
  components: {
    PhotoGrid,
    PhotoRow,
    ViewMore,
    NumOfRemaining,
    PhotoMask,
    PhotoThumb,
  },
  props: {
    height: Number,   // Výška riadku v px
    padding: Number,  // Veľkosť padding-bottom
    photos: Array,
    layoutNum: Number,
    remainingNum: Number,
    showNumOfRemainingPhotos: Boolean,
    maxRandomPercWidth: Number, // Percento, o ktoré sa môže meniť naviac šírka fotky
    widerPhotoId: Number //Poradové id fotky, ktorá má byť širšia
  },
  computed: {
    randId() {
      /* Ak je menej ako 2 fotky alebo poradie širšej fotky je -1 tak vráť -1(všetky fotky budú rovnako široké)
       * inak ak je poradie širšej fotky zadané( > 0) vráť (poradie - 1)
       * inak generuj náhodné id
       * this.widerPhotoId: 
       * poradie fotky v riadku, ktorá má byť širšia 1,2,... 
       * ak je zadané 0 generuje sa náhodne
       * ak je zadané -1 všetky fotky budú rovnaké */
      return (this.photos.length < 2 || this.widerPhotoId == -1) ? -1
                : (this.widerPhotoId > 0 ? (this.widerPhotoId - 1)
                                         : Math.floor(Math.random() * this.photos.length))
    },
    photoStyle() {
      // Náhodných maximálne this.maxRandomPercWidth percent k šírke prvku
      return {
        width: Math.floor(Math.random() * (this.maxRandomPercWidth +1)),
      };
    },
  }
}
</script>

<template>
  <photo-row :rowHeight="height" :rowPadding="padding">
    <photo-grid 
      v-for="(data, i) in photos"
      @click="$emit('itemClick', data, i)"  
      :key="i"
      :photostyle="i == randId ? photoStyle : {}"
    >
      <template v-if="showNumOfRemainingPhotos && remainingNum > 0 && data.id === (layoutNum - 1)">
        <photo-mask></photo-mask>
        <view-more>
          <num-of-remaining>{{remainingNum}}</num-of-remaining>
        </view-more>
      </template>
      <photo-thumb :thumb="data.source"></photo-thumb>
    </photo-grid>
  </photo-row>
</template>