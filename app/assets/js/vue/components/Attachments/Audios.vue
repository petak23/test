<script>
/**
 * Component audios
 * Posledna zmena 20.03.2023
 *
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 * 
 */

import axios from 'axios'

export default {
	props: {
		filePath: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			audios: null,
			id: 0,
		}
	},
	methods: {
		getAttachments() {
			let odkaz = this.$store.state.apiPath + 'documents/getvisibleattachments/' + parseInt(this.$store.state.article.id_hlavne_menu) + "?group=audios"
			axios.get(odkaz)
				.then(response => {
					//console.log(response.data.length)
					this.audios = response.data.length > 0 ? response.data : null
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
		// Generovanie url pre lazyloading obrázky
		getFileUrl(text) {
			return this.filePath + "/" + text
		},
	},
	watch: {
		'$store.state.article.id_hlavne_menu': function () {
			this.getAttachments()
		}
	},
}
</script>

<template>
	<div v-if="audios != null" class="row attachments">
		<div class="col-12">
			<hr />
			<h4>{{ $store.state.texts.clanky_h3_prilohy_audios }}:</h4>
		</div>
		<div class="col-12 col-md-6" v-for="im in audios" :key="im.id">
	    <div class="thumbnail">
	      <audio controls>
	        <source :src="getFileUrl(im.main_file)" type="audio/mp3">
	      </audio>
	      <div>
	        <strong>{{im.name}}</strong>
	        <div v-if="im.description != null" class="popis">{{ im.description }}</div>
	      </div>
	    </div>
	  </div>
	</div>
</template>

<style scoped>
</style>