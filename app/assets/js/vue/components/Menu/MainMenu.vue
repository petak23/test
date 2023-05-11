<script>
/**
 * Komponenta pre základné rozloženie.
 * Posledna zmena 14.02.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */

import vuetify from '@/front/js/vue/plugins/vuetify'
import axios from 'axios'


//for Tracy Debug Bar
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default {
	vuetify,
	components: {},
	props: {},
	data: () => ({
		submenu: null,
	}),
	computed: {},
	methods: {
		getItem(items, pom, level) {
			let su = null
			let self = this
			items.map(function (i) {
				if (i.id == pom[level]) {
					su = i
					if (typeof (su.children) != undefined && level < (pom.length -1)) {
						level++
						su = self.getItem(i.children, pom, level)
						level--
					}
				}
			})
			return su
		},
		getSubmenu2() {
			this.submenu = this.getItem(this.$store.state.main_menu, this.$store.state.main_menu_open, 0)
		}
	},
	mounted() {},
	created() {
		// Reaguje na načítanie hl. menu
		this.$root.$on('main-menu-loadet', data => {
			if (parseInt(this.$store.state.main_menu_active) != 0) {
				this.getSubmenu2()
			}
		})
	}
}
</script>

<template>
	<div class="menu_new">
		<v-treeview 
			v-if="submenu != null"
			dense 
			:items="submenu.children" 
			activatable item-key="id" 
			:active="[$store.state.main_menu_active]"
			:open="$store.state.main_menu_open"
		>
			<template v-slot:label="{ item }">
				<a :href="item.link" :title="item.name">{{ item.name }}</a>
			</template>
		</v-treeview>
	</div>
</template>