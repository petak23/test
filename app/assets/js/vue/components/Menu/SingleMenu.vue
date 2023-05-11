<script>
/** 
 * Component BWfoto_Tree_Main
 * Posledná zmena(last change): 07.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.0
 * 
 */

export default {
	props: {
		ulClass: {
			type: String,
			default: "navbar-nav mr-md-2 flex-grow-1 justify-content-end"
		}
	},
	data() {
		return {
			menu_part: null,
			single_menu: [],
		}
	},
	methods: {
		/**
		 * @param {array} items main_menu items
		 * @param {array} mmo main_menu_open 
		 * @param {int} level 
		 */
		getItem(items, mmo, level) {
			let self = this
			items.map(function (i) {
				if (i.id == mmo[level]) {
					self.single_menu = i
					if (typeof (i.children) != undefined && level < (mmo.length - 1)) {
						level++
						self.getItem(i.children, mmo, level)
						level--
					}
				}
			})
		},
		getSingleMenu() {
			this.single_menu = []
			this.getItem(this.$store.state.main_menu, this.$store.state.main_menu_open, 0)
		},
	},
	watch: {
		'$store.state.main_menu_open': function () {
			this.getSingleMenu()
		}
	},
	created() {
		// Reaguje na načítanie hl. menu
		this.$root.$on('main-menu-loadet', data => {
			this.getSingleMenu()
		})
	}
}
</script>

<template>
	<ul :class="ulClass" v-if="single_menu != null">
		<li class="nav-item"  v-for="item in single_menu.children" :key="item.id">
			<a  :href="item.link" 
					:title="item.name"
					class="nav-link btn btn-outline-secondary"
					
			><!--:class="item.getItemClass('active')"-->
				{{ item.name }}
				<br v-if="item.tooltip != null" />
				<small v-if="item.tooltip != null">{{ item.tooltip }}</small>
			</a>
		</li>
	</ul>
</template>