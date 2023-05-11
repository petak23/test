<script>
/**
 * Component attachments
 * Posledna zmena 21.03.2023
 *
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 * 
 */

import AttImages from './Images.vue'
import AttAudios from './Audios.vue'
import AttOthers from './Others.vue'


export default {
	components: {
		AttImages,
		AttAudios,
		AttOthers,
	},
	props: {
		filePath: {
			type: String,
			required: true,
		},
	},
}
</script>

<template>
	<div class="col-12">
		<att-images
			:file-path="filePath"
		></att-images>
		<att-audios
			:file-path="filePath"
		></att-audios>
		<att-others
			:file-path="filePath"
		></att-others>
	</div>
</template>