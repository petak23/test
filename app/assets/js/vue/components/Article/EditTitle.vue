<script>
/** 
 * Component EditTitle
 * Posledná zmena(last change): 27.02.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.6
 * 
 */
import axios from 'axios'

//for Tracy Debug Bar
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default {
	props: {
		button_prefix: { // Prefix classu odosielacích tlačidiel
			type: String,
			default: "main"
		},
		article: {
			type: Object,
			required: true,
		},
		modal_name: { // Názov modlneho okna
			type: String,
			default: 'EditTitleModal',
		}
	},
	data() {
		return {
			show: true,
			art_title: {
				menu_name: '',
				h1part2: '',
				view_name: '',
			},
		}
	},
	methods: {
		saveErr() {
			this.$root.$emit('flash_message', [{
				'message': 'Došlo k chybe a položka sa neuložila.',
				'type': 'danger',
				'heading': 'Oopps ...',
				'timeout': 10000,
			}])
		},
		onSubmit(event) {
			event.preventDefault()
			// Aby sa formulár odoslal, len ak je stačené tlačítko s class="main-submit"
			if (event.submitter.classList.contains(this.button_prefix + "-submit")) {
				let odkaz = this.$store.state.apiPath + 'menu/h1save/' + this.article.id
				let vm = this
				let data = {
							menu_name: this.art_title.menu_name,
							h1part2: this.art_title.h1part2,
							view_name: this.art_title.view_name,
						}
				axios.post(odkaz, {
						article: data
					})
					.then(function (response) {
						//console.log(response.data.result)
						if (response.data.result == "OK") {
							vm.$root.$emit('flash_message', [{'message':'Položka v nadpise bola uložená.', 
																								'type':'success',
																								'heading': 'Uložené ...',
																								'timeout': 5000,
																							}])
							let	td = response.data
							delete td.result
							vm.$root.$emit("reload-article-" + vm.article.id, [td])
						} else {
							vm.saveErr()
						}
						setTimeout(() => {
							vm.$bvModal.hide(vm.modal_name)
						}, 500)
					})
					.catch(function (error) {
						vm.saveErr()
						console.log(odkaz)
						console.log(error)
					});
			}
		},
		onReset(event) {
			event.preventDefault()
			if (event.explicitOriginalTarget.classList.contains(this.button_prefix + "-reset")) {
				this.$bvModal.hide(this.modal_name)
				this.getArtTitle()
			}
		},
		getArtTitle() {
			this.art_title.menu_name = this.article.menu_name
			this.art_title.h1part2 = this.article.h1part2
			this.art_title.view_name = this.article.view_name
		}
	},
	watch: {
		article: function () {
			this.getArtTitle()
		}
	},
	mounted () {
		this.getArtTitle()
	},
}
</script>

<template>
	<b-modal 
		:id="modal_name"
		centered
		:title="$store.state.texts.base_edit_title" 
		header-bg-variant="dark"
		header-text-variant="light"
		body-bg-variant="dark"
		body-text-variant="light"
		footer-bg-variant="dark"
		footer-text-variant="light" 
		:hide-footer="true" 
	>
		<b-form @submit="onSubmit" @reset="onReset" v-if="show">
			<b-form-group
				id="input-group-1"
				label="Názov zobrazený v nadpise:"
				label-for="view_name"
			>
				<b-form-input
					id="view_name"
					v-model="art_title.view_name"
					type="text"
					required
				></b-form-input>
			</b-form-group>
			<b-form-group
				id="input-group-2"
				label="Názov zobrazený v menu:"
				label-for="menu_name"
			>
				<b-form-input
					id="menu_name"
					v-model="art_title.menu_name"
					type="text"
					description="Ak necháte pole prázdne použije sa rovnaký ako pre nadpis."
				></b-form-input>
			</b-form-group>
			<b-form-group
				id="input-group-2"
				label="Podnatpis:"
				label-for="h1part2"
			>
				<b-form-input
					id="h1part2"
					v-model="art_title.h1part2"
					type="text"
				></b-form-input>
			</b-form-group>
			<b-button 
				type="submit" variant="success"
				class="mr-2" 
				:class="button_prefix + '-submit'"
			>
				Ulož
			</b-button>
			<b-button
				type="reset" variant="secondary"
				:class="button_prefix + '-reset'"
			>
				Cancel
			</b-button>
		</b-form>
	</b-modal>
</template>

<style scoped>
</style>