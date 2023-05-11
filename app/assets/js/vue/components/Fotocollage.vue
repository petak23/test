<script>
/** 
 * Component Fotocollage
 * Posledná zmena(last change): 21.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.2.3
 * Z kniznica pouzite súbory a upravene: https://github.com/seanghay/vue-photo-collage
 */
import PhotoCollageWrapper from "./vue-photo-collage/PhotoCollageWrapper.vue";
import EditSchemaRow from "./vue-photo-collage/EditSchemaRow.vue"
import axios from 'axios'

export default {
	components: {
		PhotoCollageWrapper,
		EditSchemaRow,
	},
	props: {
		filesPath: { // Adresár k súborom
			type: String,
			required: true
		}, 
		maxrandompercwidth: { // Percento, o ktoré sa môže meniť naviac šírka fotky
			type: String,
			default: 20,
		},
		myschema: String,
		edit_enabled: {
			type: String,
			default: '0'
		}
	},
	data() {
		return {
			id: 0,
			attachments: [],

			collage: { // Objekt pre koláž
				width: "",
				height: [],
				layout: [],
				photos: [],
				borderRadius: ".2rem",
				showNumOfRemainingPhotos: false,
				maxRandomPercWidth: 20,
				widerPhotoId: [], // poradie fotky v riadku, ktorá má byť širšia 1,2,... 
													// ak je zadané 0 generuje sa náhodne
													// ak je zadané -1 všetky fotky budú rovnaké
				maxPhotosToShow: 0, // Max. počet fotiek na zobrazenie pre dan schému
				uniqeRows: 0,			// Počet jedinečných riadkov (pred opakovaním)
			},
			image: {
				name: "",
				main_file: "",
				description: null,
				id_collage: 0,
			},
			schstr: "",			// Schéma v textovej podobe
			schstr_old: "",	// Záloha textovej podoby schémy
			/* Formát schémy: 
			[
				{
					// Max. šírka koláže pre ktorú platí
					max_width: 320,  
					// Počet fotiek v jednotlivých riadkoch
					schema: [2, 1, 3, 4, 4, 3, 4, 4], 
					// Výška jednotlivých riadkov v px
					height: [85, 60, 85, 60, 70, 95, 70, 60],
					// Veľkosť medzery pod daným riadkom
					padding: [2, 5, 0, 0, 0, 5, 0, 0],
					// Poradie fotky v riadku, ktorá má byť širšia ako ostatné v riadku:
					// Ak je zadané číslo väčšie ako 0 (1,2,...) tak tá konkrétna bude širšia, 
					// ak je zadané 0 generuje sa náhodne,
					// ak je zadané -1 všetky fotky v riadku budú rovnaké.
					widerPhotoId: [-1, 2, 0, 1, -1, 0, 2, 1], 
				},
				...
			],*/
			sch: [],				// Aktuálna schéma
		}
	},
	methods: {
		itemClickHandler(data, i) {
			// click event
			let odkaz = this.$store.state.apiPath + 'documents/document/' + data.id_foto
			axios.get(odkaz)
							.then(response => {
								this.image = response.data
								this.image.id_collage = data.id
								this.$bvModal.show("modal-multi-1")
							})
							.catch((error) => {
								console.error(odkaz)
								console.error(error)
							})
		},
		matchHeight () {
			this.computeLayout(this.$refs.imgDetail.clientWidth)
		},
		computeLayout(client_width) {
			let res = { 
				max_width: 0,  
				schema: [],  
				height: [],
				padding: [],  
				layout: [],
				widerPhotoId: [],
				maxPhotosToShow: 0
			};
			this.sch.forEach(x => {
				if (client_width < x.max_width && res.max_width == 0) {
					res = x
				}
			})
			res.layout = [] // Musí ostať inak nefunguje !?!
			this.collage.photos = this.attachments
			let i = this.collage.photos.length
			let r = 0 // riadok
			do {
				// Zisti počet foto v riadku. Ak by bolo 0 tak nahraď to 1
				let fr = res.schema[r] > 0 ? res.schema[r] : 1;
				res.layout.push( fr )

				r = r + 1 >= res.schema.length ? 0 : r + 1 
				i -= fr
			}
			while (i > 0);
			this.collage.width = client_width + 'px';
			this.collage.height = res.height.map(x => parseInt(x)) // Aby som z textových položiek urobil čísla
			this.collage.padding = res.padding.map(x => parseInt(x))
			this.collage.layout = res.layout.map(x => parseInt(x))
			this.collage.maxRandomPercWidth = parseInt(this.maxrandompercwidth)
			this.collage.widerPhotoId = res.widerPhotoId.map(x => parseInt(x))
			// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/Reduce
			this.collage.maxPhotosToShow = res.schema.map(x => parseInt(x)).reduce((a, b) => a + b, 0)
			this.collage.uniqeRows = res.schema.length
		},
		loadSchema() {
			let odkaz = this.$store.state.apiPath + 'menu/getfotocollagesettings/' + this.$store.state.article.id_hlavne_menu
			axios.get(odkaz)
				.then(response => {
					this.sch = response.data
					this.schstr = JSON.stringify(this.sch, null, 2)
					this.schstr_old = this.schstr
					this.loadPictures()
				})
				.catch((error) => {
					console.error(odkaz);
					console.error(error);
				});
		},
		loadPictures() {
			// Načítanie údajov priamo z DB
			let odkaz = this.$store.state.apiPath + 'documents/getfotocollage/' + this.$store.state.article.id
			axios.get(odkaz)
				.then(response => {
					this.attachments = response.data
					this.computeLayout(this.$refs.imgDetail.clientWidth)

				})
				.catch((error) => {
					console.error(odkaz);
					console.error(error);
				});
		},
		SchemaChanged(id_part) {
			let odkaz = this.$store.state.apiPath + 'menu/savefotocollagesettings/' + this.$store.state.article.id_hlavne_menu
			let vm = this
			let data = {
				data: this.sch
			}
			axios.post(odkaz, data)
				.then(function (response) {
					vm.$root.$emit('flash_message', [{
						'message': 'Schéma bola uložená.',
						'type': 'success',
						'heading': 'Podarilo sa...'
					}])
					//setTimeout(() => {
						vm.sch = response.data
						vm.computeLayout(vm.$refs.imgDetail.clientWidth)
					//}, 500)
				})
				.catch(function (error) {
					console.error(odkaz)
					console.error(error)
				});
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
		swipe(direction) {
			if (direction == 'Left' || direction == 'Up') {
				this.before()
			} else if (direction == 'Right' || direction == 'Down') {
				this.after()
			}
		},
		// Zmena id na predošlé
		before() {
			let id = this.image.id_collage <= 0 ? (this.attachments.length - 1) : this.image.id_collage - 1
			this.itemClickHandler({
				id: id,
				id_foto: this.attachments[id].id_foto
			}, 1)
		},
		// Zmena id na  nasledujúce
		after() {
			this.id = this.id >= (this.attachments.length - 1) ? 0 : this.id + 1;
			let id = this.image.id_collage >= (this.attachments.length - 1) ? 0 : this.image.id_collage + 1
			this.itemClickHandler({
				id: id,
				id_foto: this.attachments[id].id_foto
			}, 1)
		},
	},
	created() {
		window.addEventListener("resize", this.matchHeight);
	},
	destroyed() {
		window.removeEventListener("resize", this.matchHeight);
	},
	watch: {
		'$store.state.article.id_hlavne_menu': function () {
			/* Nčítanie schémy fotokoláže */
			this.loadSchema();
		}
	},
	mounted () {
		/* Naviazanie na sledovanie zmeny veľkosti stránky */
		this.matchHeight();

		/* Naviazanie na sledovanie stláčania klávesnice */
		document.addEventListener("keydown", this.keyPush);

		this.$root.$on('schema-changed', data => {
			this.sch[data[0].id_part] = data[0].data
			this.SchemaChanged(data[0].id_part)
		})
	},

};
</script>

<template>
	<section id="webThumbnails" class="row">
		<div class="col-12 vue-fotogalery">
			<ul class="nav justify-content-end mb-1" v-if="edit_enabled == '1'">
				<li class="nav-item">
					<a type="button" class="btn btn-sm btn-dark disabled" disabled>
						Fotiek: {{ attachments.length }}, max. v schéme: {{ collage.maxPhotosToShow }},
						riadkov: {{ collage.uniqeRows }}
					</a>
				</li>
				<li class="nav-item">
					<b-button 
						variant="outline-warning"
						size="sm"
						v-b-modal.edit-collage
						title="Editácia schémy">
						<i class="fas fa-pen"></i>
					</b-button>
				</li>
			</ul>
			<b-modal 
				id="edit-collage"
				v-if="edit_enabled == '1'"
				centered 
				size="xl" 
				title="Editácia schémy fotokoláže" 
				ok-only 
				header-bg-variant="dark"
				header-text-variant="light"
				body-bg-variant="dark"
				body-text-variant="light"
				footer-bg-variant="dark"
				footer-text-variant="light" 
				ref="modal1fo"
				:hide-footer="true"
			>
				<div
					class="accordion"
					role="tablist"
					v-for="(s, index) in sch"
					:key="index"
				>
					<edit-schema-row
						:row="s"
						:id_part="index"
					>
					</edit-schema-row>
				</div>
			</b-modal>

			<div ref="imgDetail" id="imgDetail"> 
				<photo-collage-wrapper 
					gapSize="6px"
					@itemClick="itemClickHandler"
					v-bind="collage">
				</photo-collage-wrapper>
			</div>
			
			<b-modal  id="modal-multi-1" centered size="xl" 
								:title="image.name" ok-only
								modal-class="lightbox-img"
								ref="modal1fo">
				<div class="modal-content">
					<div class="modal-body my-img-content">  
						<img :src="filesPath + image.main_file" 
									:alt="image.name" 
									id="big-main-img"
									class="" />
						<div class="text-center description" v-if="image.description != null">
							{{ image.description }}
						</div>
					</div>
					<div class="arrows-overlay">
						<div class="arrows-l" @click="before">
							<a href="#" class="text-light" :title="$store.state.texts.galery_arrows_before">&#10094;
							</a>
						</div>
						<div class="go-to-hight" v-touch="{
							left: () => swipe('Left'),
							right: () => swipe('Right'),
							up: () => swipe('Up'),
							down: () => swipe('Down')
						}">
						</div>
						<div class="arrows-r flex-row-reverse" @click="after">
							<a href="#" class="text-light" :title="$store.state.texts.galery_arrows_after">&#10095;
							</a>
						</div>
					</div>
				</div>
			</b-modal>
		</div>
	</section>
</template>

<style lang="scss" scoped>
	img {
		max-width: 80vw;
		height: 80vh;
	}
</style>