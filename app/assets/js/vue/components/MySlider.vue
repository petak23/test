<script>
/** 
 * Component Slider
 * Posledná zmena(last change): 14.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 */

import axios from 'axios'

export default {
	props: {
		filesPath: { // Adresár k súborom
			type: String,
			required: true
		},
	},
	data() {
		return {
			items: null,
		}
	},
	methods: {
		getSlider() {
			let odkaz = this.$store.state.apiPath + 'slider/getall/1'
			axios.get(odkaz)
				.then(response => {
					//console.log(response.data)
					this.items = response.data
					if (this.items !== null) { 
						let show = this.findIn()
						if (show != null) {
							this.$root.$refs.myslider.style.backgroundImage = 'url(' + this.filesPath + show.subor + ')'
						}
					}
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
		/*
	 	 * Najdenie poloziek slidera */
		findIn() {
			let vysledok = false
			let _v = null
			// Nájdi priamo daný klúč
			let sl_item = this.items.find(el => parseInt(el.zobrazenie) == this.$store.state.main_menu_active)
			if (sl_item == undefined) { // Ak nieje ...
				let vysa = [];
				this.items.forEach((item, index) => {
					let s = item.zobrazenie
					if (s === null) {
						vysledok = true
					} else {
						_v = item.zobrazenie.indexOf(",") !== -1 ? item.zobrazenie.split(",") : item.zobrazenie
						vysledok = false
						if (Array.isArray(_v)) {
							_v.forEach((z) => {
								vysledok = this.zisti(parseInt(z)) ? true : vysledok
							})
						} else {
							vysledok = this.zisti(parseInt(_v));
						}
					}
					if (vysledok) {
						vysa.push(index);
					}
				})
				sl_item = vysa.length ? this.items[vysa[0]] : null
			}
			return sl_item
		},
		/* Pre vyhodnotenie zobrazenia položky z*/
		zisti(z)
		{
			return z == null ? true
				: (z == 0 ? true
					: (z > 0 && (this.$store.state.main_menu_open.find(el => el == parseInt(z) !== undefined) )))
		}
	},
	watch: {
		'$store.state.main_menu_active': function() {
			if (this.$store.state.main_menu_active !== undefined) {
				this.getSlider()
			}
		}
	},
};
</script>
<template>
</template>