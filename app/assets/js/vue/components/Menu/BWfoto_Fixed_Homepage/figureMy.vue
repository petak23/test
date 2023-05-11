<script>
/** 
 * Component BWfoto_Fixed_Homepage -> figure
 * Posledná zmena(last change): 13.03.2023
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
		item: {
			type: Object,
			required: true,
		},
		main_class: {
			type: String,
			default: "",
		},
		avatarDir: { // Kompletná cesta vrátane basePath
			type: String,
			default: ""
		},
		if_part: {
			type: Boolean,
			default: false
		},
	},
	computed: {
		avatar() {
			return this.avatarDir.length && this.item.avatar != null ? this.avatarDir + this.item.avatar : false
		}
	},
}
</script>

<template>
	<figure 
		class="w-img"
		:class="main_class"
	>
		<a 
			:href="item.link"
			:title="item.name">
			<img 
				v-if="avatar"
				:src="avatar"
				class="img-fluid"
				:alt="item.name"
			>
			<figcaption class="text-center w-100">
				<h2 
					class="py-2"
					:class="if_part ? 'black-curtain m-0 h3' : 'white-curtain text-body'"
				>
					{{ item.name }}
				</h2>
			</figcaption>
		</a>
	</figure>
</template>