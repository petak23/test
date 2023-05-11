<script>
import part_smallVue from './BWfoto_Fixed_Homepage/part_small.vue'
/** 
 * Component BWfoto_Fixed_Homepage
 * Posledná zmena(last change): 13.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.0
 * 
 */
import part_small from "./BWfoto_Fixed_Homepage/part_small.vue"
import figureMy from "./BWfoto_Fixed_Homepage/figureMy.vue"

export default {
	components: {
		part_small,
		figureMy,
	},
	props: {
		part: {
			type: String,
			default: "1",
		},
		ulClass: {
			type: String,
			default: "navbar-nav mr-md-2 flex-grow-1 justify-content-end"
		},
		avatarDir: {	// Kompletná cesta vrátane basePath
			type: String,
			required: true,
		}
	},
	data() {
		return {
			menu_part: null,
		}
	},
	computed: {
		menu() {
			const steps = [0, 3, 1, 4, 2, 5]
			let out = []
			let tmp = { f: null, s: null}
			let menu_s = []
			if (this.menu_part != null) {
				steps.forEach(i => {
					menu_s.push(this.menu_part.children[i])
				})
				menu_s.forEach((item, index) => {
					if (index % 2 == 0) {
						tmp.f = item
					} else {
						tmp.s = item
						out.push(tmp)
						tmp = { f: null, s: null }
					}
				})
			}
			return out
		}
	},
	created() {
		// Reaguje na načítanie hl. menu
		this.$root.$on('main-menu-loadet', data => {
			this.$store.state.main_menu.map(item => {
				if (item.id == -1*parseInt(this.part)) this.menu_part = item
			})
		})
	}
}
</script>

<template>
	<div>

	<!-- kategorie webu lg+ -->
	<section id="webParts" class="container pb-3" v-if="menu_part != null">
	  <div class="row w-100">
	    <div
				v-for="(node, index) in menu"
				:key="index"
				class="col d-flex" 
				:class="index != 1 ? 'flex-column justify-content-between' : 'flex-column-reverse justify-content-around'"
				
			>
				<figure-my
					:item="node.f"
					:avatarDir="avatarDir"
					:if_part="index != 1"
				>
				</figure-my>
				<part_small
					:item="node.s"
					main_class="d-flex flex-column justify-content-center"
					:bolder="node.s.id == 5"
				>
				</part_small>
	    </div>
	  </div>
	</section>

	<!-- kategorie webu md- -->
	<section id="webParts-md" class="container pb-3" v-if="menu_part != null">
		<div class="row w-100">
			<div class="col-md d-flex flex-column justify-content-between">
				<figure-my
					:item="menu_part.children[0]"
					:avatarDir="avatarDir"
					:if_part="true"
				>
				</figure-my>
				<figure-my
					:item="menu_part.children[1]"
					:avatarDir="avatarDir"
				>
				</figure-my>
			</div>
			<div class="col-md d-flex flex-column justify-content-between">
				<figure-my
					:item="menu_part.children[2]"
					:avatarDir="avatarDir"
					:if_part="true"
				>
				</figure-my>
				<figure class="d-flex flex-column justify-content-start justify-content-md-between">
					<part_small	:item="menu_part.children[3]"></part_small>
					<part_small
						:item="menu_part.children[4]"
						:bolder="true"
					></part_small>
					<part_small	:item="menu_part.children[5]"></part_small>
				</figure>
			</div>
		</div>
	</section>
	</div>
</template>