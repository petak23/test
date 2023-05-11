<script>
/** 
 * Component Fotopanorama
 * Posledná zmena(last change): 21.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.1.3
 */

import axios from 'axios'

export default {
	props: {
		first_id: { // Ak je nastavené tak sa zobrazí obrázok ako prvý
			type: String,
			default: "0",
		},
		filesPath: { // Adresár k súborom
			type: String,
			required: true
		},
	},
	data() {
		return {
			id: 0,
			article: {},
			attachments: [{ // Musí byť nejaký nultý objekt inak je chyba...
				description: null,
				id: 0,
				main_file: "",
				name: "",
				thumb_file: "",
				type: "",
				web_name: ""
			}],
		}
	},
	methods: {
		// Zmena id
		changebig: function(id) {
			this.id = id
		},
		modalchangebig (id) {
			this.id = id;
			this.$bvModal.show("modal-multi-1")
		},
		// Zmena id na predošlé
		before: function() {
			this.id = this.id <= 0 ? (this.attachments.length - 1) : this.id - 1;
		},  
		// Zmena id na  nasledujúce
		after: function() {
			this.id = this.id >= (this.attachments.length - 1) ? 0 : this.id + 1;
		}, 
		closeme: function() {
			this.$bvModal.hide("modal-multi-2");
		},
		keyPush(event) {
			switch (event.key) {
				case "ArrowLeft":
					this.before();
					break;
				case "ArrowRight":
					this.after();
					break;
			}
		},
		// Generovanie url pre lazyloading obrázky
		getImageUrl(text) {
			return this.filesPath + text
		},
		border_compute(border) {
			let pom = border != null && border.length > 2 ? border.split("|") : ['', '0'];
			return "border: " + pom[1] + "px solid " + (pom[0].length > 2 ? (pom[0]) : "inherit")
		},
		getMainArticle() {
			let odkaz = this.$store.state.apiPath + 'menu/getonehlavnemenuarticle/' + this.$store.state.article.id_hlavne_menu
			axios.get(odkaz)
				.then(response => {
					this.article = response.data
					//console.log(this.article)
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
		getAttachments() {
			let odkaz = this.$store.state.apiPath + 'documents/getfotogalery/' + this.$store.state.article.id_hlavne_menu
			axios.get(odkaz)
				.then(response => {
					//console.log(response.data)
					this.attachments = response.data
					if (parseInt(this.first_id) > 0) { // Ak mám first_id tak k nemu nájdem položku v attachments
						this.getFirstId(parseInt(this.first_id))
					}
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
		getFirstId(id) {
			Object.keys(this.attachments).forEach(ma => {
				if (this.attachments[ma].id === id) {
					this.id = ma
				}
			});
		}
	},
	created() {
		if (parseInt(this.first_id) > 0) { // Ak mám first_id tak k nemu nájdem položku v attachments
			Object.keys(this.attachments).forEach(ma => { 
				if (this.attachments[ma].id == this.first_id) {
					this.id = ma;
				}
			});
		}
	},
	computed: {
		border_a() {
			return this.border_compute(this.article.border_a)
		},
		border_b() {
			return this.border_compute(this.article.border_b)
		},
		border_c() {
			return this.border_compute(this.article.border_c)
		}
	},
	watch: {
		'$store.state.article.id_hlavne_menu': function () {
			/* Načítanie údajov hl. menu z DB */
			this.getMainArticle()

			this.getAttachments()
		}
	},
	mounted () {
		// Naviazanie na sledovanie stláčania klávesnice
		document.addEventListener("keydown", this.keyPush);
	},

};
</script>

<template>
	<section id="webThumbnails" class="row">
		<div class="col-12 main-win">
			<div class="row">
				<h4 class="col-12 bigimg-name">
					{{ attachments[id].name }}
				</h4>
			</div>
			<div class="row">
				<div class="col-12 thumbpanorama" id="imgDetail" ref="imgDetail">
					<div v-for="(im, index) in attachments" :key="im.id">
						<a  v-if="im.type == 'menu'" 
								:href="im.web_name" 
								:title="im.name">
							<b-img-lazy
								:src="getImageUrl(im.main_file)"
								:alt="im.name" class="img-fluid podclanok">
							></b-img-lazy>
							<h4 class="h4-podclanok">{{ im.name }}</h4>
						</a>
						<video v-else-if="im.type == 'attachments3'"
									class="video-priloha" 
									:src="filesPath + im.main_file" 
									:poster="filesPath + im.thumb_file"
									type="video/mp4" controls="controls" preload="none">
						</video>
						<button v-else-if="im.type == 'attachments1'" 
										:title="im.name">
							<b-img-lazy
								:src="getImageUrl(im.thumb_file)" 
								:alt="im.name" 
								class="img-fluid a3">
							></b-img-lazy>
							<br><h6>{{ im.name }}</h6>
						</button>
						<button v-else-if="(im.type == 'attachments2' || im.type == 'product')"
										@click.prevent="modalchangebig(index)" 
										type="button" 
										class="btn btn-link">
							<b-img-lazy
								:src="getImageUrl(im.thumb_file)" 
								:alt="im.name" 
								class="img-fluid a12">
							></b-img-lazy>
						</button>
					</div>
				</div>
			</div>

			<b-modal  id="modal-multi-1" centered size="xl" 
								:title="attachments[id].name" ok-only
								modal-class="lightbox-img"
								ref="modal1fo">
				<div class="modal-content">
					<div class="modal-body my-img-content">
						<div class="border-a" :style="border_a">
							<div class="border-b" :style="border_b">
								<img :src="filesPath + attachments[id].main_file" 
											:alt="attachments[id].name" 
											id="big-main-img"
											class="border-c" 
											:style="border_c" />
							</div>
						</div>
						<div class="text-center description" v-if="attachments[id].description != null">
							{{ attachments[id].description }}
						</div>
					</div>
					<div class="arrows-overlay">
						<div class="arrows-l"
								@click="before">
							<a href="#" class="text-light"   
									:title="$store.state.texts.galery_arrows_before">&#10094;
							</a>
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
	</section>
</template>

<style lang="scss" scoped>
.thumbpanorama {
	display: grid;
	grid-template-columns: repeat(1, 1fr);
	grid-gap: 0.5rem;
	overflow: auto;
	max-height: 80vh;
	grid-auto-rows: 7rem;

	> div{
		position: relative;
		background-color: rgba(44,44,44,1.00);
		padding: 1rem;
	}
	img{
		position: absolute;
		max-width: 90%; 
		max-height: 90%;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		border: solid 3px #ddd;
		color: transparent;
	}	
	img.podclanok {
		opacity: .5;
	}
	.h4-podclanok {
		position: absolute;
		max-width: 90%; max-height: 90%;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		color: #ddd;
		text-align: center;
	}
}
.btn:focus {
	box-shadow: none;
}
</style>