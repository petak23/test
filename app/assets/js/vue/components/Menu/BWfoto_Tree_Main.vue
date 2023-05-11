<script>
/** 
 * Component BWfoto_Tree_Main
 * Posledná zmena(last change): 27.02.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2
 * 
 */

export default {
	props: {
		part: {
			type: String,
			default: "1",
		},
		ulClass: {
			type: String,
			default: "navbar-nav mr-md-2 flex-grow-1 justify-content-end"
		}
	},
	data() {
		return {
			menu_part: null,
		}
	},
	created() {
		// Reaguje na načítanie hl. menu
		this.$root.$on('main-menu-loadet', data => {
			this.$store.state.main_menu.map(item => {
				if (item.id == this.part) this.menu_part = item
			})
		})
	}
}
</script>

<template>
	<ul :class="ulClass" v-if="menu_part != null">
		<li class="nav-item"  v-for="item in menu_part.children" :key="item.id">
			<a  :href="item.link" 
					:title="item.name"
					class="nav-link"
					
			><!--:class="item.getItemClass('active')"-->
				{{ item.name }}
				<br v-if="item.tooltip != null" />
				<small v-if="item.tooltip != null">{{ item.tooltip }}</small>
			</a>
		</li>
	</ul>
</template>