<script>
/** 
 * Component Fotogalery
 * Posledná zmena(last change): 30.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.1.4
 */

import axios from 'axios' 

export default {
	props: {
		first_id: { // Ak je nastavené tak sa zobrazí obrázok ako prvý
			type: String,
			default: "0",
		},
		large: String,
		filesPath: { // Adresár k súborom
			type: String,
			required: true
		},
	},
	data() {
		return {
			id: 0,
			square: 0,
			wid: 0,
			uroven: 0, // Premenná sleduje uroveň zobrazenia
			article: {},
			attachments: [{ // Musí byť nejaký nultý objekt inak je chyba...
				description: null,
				id: 0,
				main_file: "",
				name: "",
				thumb_file: "",
				type: "",
				web_name: "",
				liked: false
			}],
			liked: false,
		}
	},
	methods: {
		// Zmena id
		changebig(id) {
			this.id = id
			this.my_liked()
		},
		modalchangebig (id) {
			this.id = id;
			this.$bvModal.show("modal-multi-1")
		},
		openmodal2 () {
			if (this.wid > 0) this.$bvModal.show("modal-multi-2")
		},
		swipe (direction) {
			//console.log(direction)
			if (direction == 'Left' || direction == 'Up') {
				this.before()
			} else if (direction == 'Right' || direction == 'Down') {
				this.after()
			}
		},
		// Zmena id na predošlé
		before() {
			this.id = this.id <= 0 ? (this.attachments.length - 1) : this.id - 1;
			this.my_liked();
		},  
		// Zmena id na  nasledujúce
		after() {
			this.id = this.id >= (this.attachments.length - 1) ? 0 : this.id + 1;
			this.my_liked();
		}, 
		closeme: function(no) {
			this.$bvModal.hide("modal-multi-" + no);
		},
		matchHeight () {
			let height = this.$refs.imgDetail.clientHeight;
			let width = this.$refs.imgDetail.clientWidth;
			let height2 = parseInt(window.innerHeight * 0.8);
			let h = height2 > height ? height2 : height;
			//console.log(h, width)
			this.square = (h > width ? width-20 : h);
			this.wid = width;
		},
		urovenUp () { // Funkcia pre zmenu úrovne o +1 na max. 2
			this.uroven += this.uroven < 2 ? 1 : 0;;
		},
		urovenDwn () {// Funkcia pre zmenu úrovne o -1 na min. 0
			this.uroven -= this.uroven > 0 ? 1 : 0;
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
						this.my_liked()
						if (this.wid == 0) {
							this.modalchangebig(this.id)
						}
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
		},
		saveLiked() {
			let item = this.attachments[this.id]
			//console.log(item)
			this.$root.$emit("product-like", [{
				id_product: item.id,
				id_article: this.$store.state.article.id_hlavne_menu,
				source: item.main_file,
				name: item.name,
			}])
			this.attachments[this.id].liked = this.attachments[this.id].liked ? false : true
			this.liked = this.attachments[this.id].liked
		},
		my_liked() {
			this.attachments[this.id].liked = this.$session.has('like-' + this.attachments[this.id].id)
			this.liked = this.attachments[this.id].liked
			//console.log(this.liked)
		}

	},
	created() {
		window.addEventListener("resize", this.matchHeight);
	},
	destroyed() {
		window.removeEventListener("resize", this.matchHeight);
	},
	computed: {
		large_thumbs() {
			return this.large == "large"
		},
		border_a() {
			return this.border_compute(this.article.border_a)
		},
		border_b() {
			return this.border_compute(this.article.border_b)
		},
		border_c() {
			return this.border_compute(this.article.border_c)
		},
	},
	watch: {
		'$store.state.article.id_hlavne_menu': function () {
			/* Načítanie údajov hl. menu z DB */
			this.getMainArticle()

			this.getAttachments()
		}
	},
	mounted () {
		/* Naviazanie na sledovanie zmeny veľkosti stránky */
		this.matchHeight();

		/* Naviazanie na sledovanie stláčania klávesnice */
		document.addEventListener("keydown", this.keyPush);

		/* Naviazanie funkcií na $emit na root elemente pre zobrazenie/skrytie modálneho okna fotogalérie 
		 * najdené na: https://stackoverflow.com/questions/50181858/this-root-emit-not-working-in-vue */
		this.$root.$on("bv::modal::shown", this.urovenUp);
		this.$root.$on("bv::modal::hidden", this.urovenDwn);
		
		this.$root.$on("product-like-del-all", this.my_liked);
	},

};
</script>

<template>
	<section id="webThumbnails" class="row">
		<div class="col-12 vue-fotogalery main-win" v-if="attachments.length > 0">
			<div class="row" v-if="wid > 0">
				<h4 class="col-8 bigimg-name d-flex justify-content-between">
					{{ attachments[id].name }}
					<button 
						v-if="attachments[id].type == 'product'"
						type="button"
						class="btn align-right"
						:class="liked ? 'btn-success' : 'btn-outline-warning'"				
						@click="saveLiked()"
						>
						<i 
							class="fa-solid"
							:class="liked ? 'fa-heart' : 'fa-thumbs-up'"
						></i>
					</button>
				</h4>
				<div class="col-4">&nbsp;</div>
			</div>
			<div class="row">
				<div class="d-none d-sm-flex justify-content-center col-sm-8 detail" ref="imgDetail" id="imgDetail"
						v-if="large_thumbs == false">
					<div id="squarePlace" 
							v-bind:style="{height: square + 'px', width: square + 'px'}">
						<a  v-if="attachments[id].type == 'menu'"
								:href="attachments[id].web_name" 
								:title="attachments[id].name">
							<img  :src="filesPath + attachments[id].main_file" 
										:alt="attachments[id].name" class="img-fluid">
							<h4>{{ attachments[id].name }}</h4>
						</a>
						<video v-if="attachments[id].type == 'attachments3'"
									class="video-priloha" 
									:src="filesPath + attachments[id].main_file" 
									:poster="filesPath + attachments[id].thumb_file"
									type="video/mp4" controls="controls" preload="none">
						</video>
						<a v-else-if="attachments[id].type == 'attachments1'"
								:title="attachments[id].name"
								:href="filesPath + attachments[id].main_file"
								target="_blank"
								class="for-pdf"
										>
							<img :src="filesPath + attachments[id].thumb_file" 
									:alt="attachments[id].name" class="img-fluid">
							<br><h6>{{ attachments[id].name }}</h6>
						</a>  
						<button v-else-if="attachments[id].type == 'attachments2' || attachments[id].type == 'product'"
										v-b-modal.modal-multi-1
										type="button" class="btn btn-link">
							<img :src="filesPath + attachments[id].main_file" 
									:alt="attachments[id].name" class="img-fluid">
						</button>
					</div>
				</div>
				<div class="col-12 thumbgrid" 
						:class="{'thumbs-large': large_thumbs, 'col-sm-4': !large_thumbs}">
					<div v-for="(im, index) in attachments" :key="im.id">
						<a  v-if="wid > 0" 
								@click.prevent="changebig(index)" href=""
								:title="'Odkaz' + (im.type == 'menu' ? im.view_name : im.name)"
								class="pok thumb-a ajax" 
								:class="index == id ? 'selected' : ''">
							<b-img-lazy
								:src="getImageUrl(im.thumb_file)"
								:alt="im.name" class="img-fluid">
							></b-img-lazy>
						</a>
						<a  v-else-if="wid == 0 && im.type == 'menu'" 
								:href="im.web_name" 
								:title="im.name">
							<b-img-lazy
								:src="getImageUrl(im.main_file)"
								:alt="im.name" class="img-fluid podclanok">
							></b-img-lazy>
							<h4 class="h4-podclanok">{{ im.name }}</h4>
						</a>
						<video v-if="wid == 0 && im.type == 'attachments3'"
									class="video-priloha" 
									:src="filesPath + im.main_file" 
									:poster="filesPath + im.thumb_file"
									type="video/mp4" controls="controls" preload="none">
						</video>
						<button v-else-if="wid == 0 && im.type == 'attachments1'" 
										:title="im.name">
							<b-img-lazy
								:src="getImageUrl(im.thumb_file)" 
								:alt="im.name" 
								class="img-fluid a3">
							></b-img-lazy>
							<br><h6>{{ im.name }}</h6>
						</button>
						<button v-else-if="wid == 0 && (im.type == 'attachments2' || im.type == 'product')"
										@click.prevent="modalchangebig(index)" 
										type="button" 
										class="btn btn-link"
										:class="index == id ? 'selected' : ''"
						>
							<b-img-lazy
								:src="getImageUrl(im.thumb_file)" 
								:alt="im.name" 
								class="img-fluid a12">
							></b-img-lazy>
						</button>
					</div>
				</div>
			</div> 
			<div class="row d-none d-sm-inline-block" v-if="wid > 0">
				<div class="col-12 bigimg-description popis">{{ attachments[id].description }}</div>
			</div>

			<b-modal  id="modal-multi-1" centered size="xl" 
								ok-only
								modal-class="lightbox-img"
								ref="modal1fo">
				<template #modal-header>
					<h5 class="modal-title">{{ attachments[id].name}}</h5>
					<button 
							v-if="attachments[id].type == 'product'"
							type="button"
							class="btn align-right"
							:class="liked ? 'btn-success' : 'btn-outline-warning'"				
							@click="saveLiked()"
							>
							<i 
								class="fa-solid"
								:class="liked ? 'fa-heart' : 'fa-thumbs-up'"
							></i>
						</button>
					<button 
						type="button" aria-label="Close" 
						class="btn btn-outline-warning mr-5"
						@click="closeme(1)"
					>
						<i class="fa-solid fa-xmark"></i>
					</button>
				</template>
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
						<div class="go-to-hight"
								v-touch="{
									left: () => swipe('Left'),
									right: () => swipe('Right'),
									up: () => swipe('Up'),
									down: () => swipe('Down')
								}"
								@click="openmodal2">
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

			<b-modal id="modal-multi-2" centered size="xl" ok-only >
				<img :src="filesPath + attachments[id].main_file" :alt="attachments[id].name" @click="closeme(2)">
			</b-modal>
		</div>
	</section>
</template>