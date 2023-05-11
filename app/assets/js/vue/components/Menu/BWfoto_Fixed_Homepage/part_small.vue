<script>
/** 
 * Component BWfoto_Fixed_Homepage -> part_small
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
			default: "h-md-25",
		},
		bolder: {
			type: Boolean,
			default: false
		}
	},
	computed: {
		iclass() {
			return 'fa-' + this.item.node_class + this.bolder ? 'bolder' : ''  
		}
	},
}
</script>

<template>
	<a 
		:href="item.link" 
		:title="item.name" 
		class="part-small"
		:class="main_class">
		<h2 class="h3 text-center">
			<i
				class="fas mr-1"  
				:class="iclass">
			</i>
			{{ item.name }}
		</h2>
	</a>
</template>