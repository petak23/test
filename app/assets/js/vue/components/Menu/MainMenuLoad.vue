<script>
/**
 * Komponenta pre načítanie hl. menu a textov prekladov.
 * Posledna zmena 23.02.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */

import axios from 'axios'


//for Tracy Debug Bar
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default {
	props: {
		apiPath: {
			type: String,
			required: true
		},
		main_menu_active: { //Aktuálna aktívna polozka
			type: String,
			default: 0,
		},
		id_hlavne_menu_lang: {
			type: String,
			default: 0,
		},
	},
	data: () => ({
		in_path: false,
	}),
	computed: {},
	methods: {
		convert(itemsObject) {
			return Object.values(itemsObject).map(item => ({
				...item,
				children: item.children ? this.convert(item.children) : undefined,
			}));
		},
		getpath(item) {
			var self = this
			item.map(function(i) {
				if (self.in_path == false) {
					if (i.id == self.main_menu_active) {
						self.$store.commit('SET_PUSH_MAIN_MENU_OPEN', i.id)
						self.in_path = true
					} else if (typeof i.children !== 'undefined' && i.children.length > 0) {
						self.getpath(i.children)
						if (self.in_path) {
							self.$store.commit('SET_PUSH_MAIN_MENU_OPEN', i.id)
						}
					}
				}
			})
		},
		getMenu() {
			let odkaz = this.apiPath + 'menu/getmenu/0/Front'
			axios.get(odkaz)
				.then(response => {
					this.$store.dispatch('changeMainMenu', this.convert(response.data))
					//this.$store.commit('SET_INIT_MAIN_MENU', this.convert(response.data))
					this.$store.commit('SET_INIT_MAIN_MENU_OPEN', [])
					this.getpath(this.$store.state.main_menu)
					this.in_path = false
					this.$store.commit('SET_REVERSE_MAIN_MENU_OPEN')
					this.$root.$emit("main-menu-loadet", [])
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},

		// Načítanie prekladov textov
		getTexts() {
			let odkaz = this.apiPath + 'lang/gettexts' 
			let vm = this
			let data = { texts: this.$store.state.texts_to_load }
			axios.post(odkaz, data)
				.then(function (response) {
					//console.log(response.data)
					vm.$store.commit('SET_INIT_TEXTS', response.data)
				})
				.catch(function (error) {
					console.log(odkaz)
					console.log(error)
				});
		},
		// Načítanie aktuálneho článku
		getArticle() {
			let odkaz = this.apiPath + 'menu/getonemenuarticle/' + this.id_hlavne_menu_lang
			axios.get(odkaz)
				.then(response => {
					this.$store.commit('SET_INIT_ARTICLE', response.data)
					//console.log(response.data)
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
	},
	watch: {
		// Zapísanie aktívnej položky menu
		main_menu_active: function (newMainMenuActive) {
			this.$store.commit('SET_MAIN_MENU_ACTIVE', parseInt(this.main_menu_active))
		}
	},
	mounted() {
		// Zapísanie apiPath
		this.$store.commit('SET_INIT_API_PATH', this.apiPath)

		// Zapísanie aktívnej položky menu
		this.$store.commit('SET_MAIN_MENU_ACTIVE', parseInt(this.main_menu_active))
		
		// Načítanie údajov priamo z DB
		this.getMenu()

		// Načítanie prekladov textov
		this.getTexts()

		// Načítanie aktuálneho článku
		this.getArticle()

		this.$root.$on('reload-main-menu', data => {
			this.getMenu()
		})
	},
}
</script>

<template></template>