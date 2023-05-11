<script>
/**
 * Component Images.
 * Posledna zmena 21.03.2023
 *
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 * 
 */

import axios from 'axios'

export default {
	props: {
		filePath: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			images: null,
			id: 0,
		}
	},
	methods: {
		getAttachments() {
			let odkaz = this.$store.state.apiPath + 'documents/getvisibleattachments/' + parseInt(this.$store.state.article.id_hlavne_menu)
			axios.get(odkaz)
				.then(response => {
					//console.log(response.data)
					this.images = response.data.length > 0 ? response.data : null
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
		// Zmena id
		changebig(id) {
			this.id = id
		},
		// Generovanie url pre lazyloading obrázky
		getImageUrl(text) {
			return this.filePath + "/" + text
		},
		keyPush(event) {
			if (this.uroven <= 1) {
				switch (event.key) {
					case "ArrowLeft":
						this.before();
						break;
					case "ArrowRight":
						this.after();
						break;
				}
			}
		},
		modalchangebig(id) {
			this.id = id;
			this.$bvModal.show("modal-images-attachments")
		},
		swipe(direction) {
			//console.log(direction)
			if (direction == 'Left' || direction == 'Up') {
				this.before()
			} else if (direction == 'Right' || direction == 'Down') {
				this.after()
			}
		},
		// Zmena id na predošlé
		before() {
			this.id = this.id <= 0 ? (this.images.length - 1) : this.id - 1;
		},
		// Zmena id na  nasledujúce
		after() {
			this.id = this.id >= (this.images.length - 1) ? 0 : this.id + 1;
		},
	},
	watch: {
		'$store.state.article.id_hlavne_menu': function () {
			this.getAttachments()
		}
	},
}
</script>

<template>
	<div v-if="images != null" class="row attachments">
		<div class="col-12">
			<hr />
			<h4>{{ $store.state.texts.clanky_h3_prilohy_images }}:</h4>
		</div>
		<div class="col-12 thumbgrid">
			<div v-for="(im, index) in images" :key="im.id">
				<a  @click.prevent="modalchangebig(index)"
						href="#"
						:title="im.name"
						class="thumb-a" 
						:class="index == id ? ', selected' : null">
					<b-img-lazy
						:src="getImageUrl(im.thumb_file)"
						:alt="im.name" class="img-fluid">
					></b-img-lazy>
				</a>
			</div>
			<b-modal  id="modal-images-attachments" centered size="xl" 
								:title="images[id].name" ok-only
								modal-class="lightbox-img"
								ref="modal1fo">
				<div class="modal-content">
					<div class="modal-body my-img-content">

						<img :src="getImageUrl(images[id].main_file)" 
									:alt="images[id].name" 
									id="big-main-img"
									/>
						<div class="text-center description" v-if="images[id].description != null">
							{{ images[id].description }}
						</div>
					</div>
					<div class="arrows-overlay">
						<div class="arrows-l"
								@click="before">
							<a href="#" class="text-light"   
									:title="$store.state.texts.galery_arrows_before">&#10094;
							</a>
						</div>
						<div class="go-to-hight"
								v-touch="{
									left: () => swipe('Left'),
									right: () => swipe('Right'),
									up: () => swipe('Up'),
									down: () => swipe('Down')
								}">
						</div>
						<div class="arrows-r flex-row-reverse"
								@click="after">
							<a href="#" class="text-light"
									:title="$store.state.texts.galery_arrows_after">&#10095;
							</a>
						</div>
					</div>
				</div>
			</b-modal>
		</div>
	</div>
</template>

<style scoped>
.thumbgrid {
	max-height: inherit;
	overflow: inherit;
}
</style>