<script>
/**
 * Komponenta pre navigáciu "odrobinky".
 * Posledna zmena 27.02.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */

export default {
	components: {},
	props: {
		homepage: {
			type: String,
			required: true,
		}
	},
	data: () => ({
		submenu: [],
	}),
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
					self.submenu.push(i)
					if (typeof (i.children) != undefined && level < (mmo.length - 1)) {
						level++
						self.getItem(i.children, mmo, level)
						level--
					}
				}
			})
		},
		getBreadcrumb() {
			this.submenu = []
			this.getItem(this.$store.state.main_menu, this.$store.state.main_menu_open, 0)
			//console.log(this.submenu)
		},
	},
	created() {
		// Reaguje na načítanie hl. menu
		this.$root.$on('main-menu-loadet', data => {
			if (parseInt(this.$store.state.main_menu_active) != 0) {
				this.getBreadcrumb()
			}
		})
	}
}
</script>

<template>
	<div class="row">
		<b-breadcrumb 
			v-if="submenu !== null && submenu.length > 1"
			class="col breadcrumb-main"
		>
			<b-breadcrumb-item
				v-for="(ia, index) in submenu"
				:key="index"
				:href="ia.link"
				:active="index == (submenu.length - 1)"
			>
				<b-dropdown 
					v-if="typeof (ia.children) !== 'undefined' && ia.children.length && index != (submenu.length - 1)"
					split
					size="sm"
					split-variant="link"
					variant="link"
					:text="ia.name"
					:split-href="index != (submenu.length - 1) ? ia.link : null"
					class="m-0"
				> 
					<b-dropdown-item 
						v-for="dit in ia.children"
						:key="dit.id"
						:href="dit.link"
					>
						{{ dit.name }}
					</b-dropdown-item>
				</b-dropdown>
				<span v-else>{{ ia.name }}</span>
			</b-breadcrumb-item>
		</b-breadcrumb>
	</div>

</template>