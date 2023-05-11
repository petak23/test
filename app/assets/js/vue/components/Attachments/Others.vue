<script>
/**
 * Component others
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
			files: null,
			id: 0,
		}
	},
	methods: {
		getAttachments() {
			let odkaz = this.$store.state.apiPath + 'documents/getvisibleattachments/' + parseInt(this.$store.state.article.id_hlavne_menu) + "?group=others"
			axios.get(odkaz)
				.then(response => {
					//console.log(response.data.length)
					this.files = response.data.length > 0 ? response.data : null
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
	<div v-if="files != null" class="row attachments">
		<div class="col-12">
			<hr />
			<h4>{{ $store.state.texts.clanky_h3_prilohy_others }}:</h4>
		</div>
		<div class="col-12 col-md-3 mb-2" v-for="im in files" :key="im.id">
	    <div class="card text-white bg-dark">
	      <div class="card-header">
	        <h5 class="card-title">{{im.name}}</h5>
	      </div>
	      <div class="card-body" v-if="im.description != null">
	        <p class="card-text">{{im.description}}</p>
	      </div>
	      <div class="card-footer">
	        <a 
						:href="im.fileDownload" 
						class="btn btn-outline-success" 
						:title="im.name + ' - ' + $store.state.texts.clanky_dokument_download"
					>
						<i class="fas fa-download"></i>
					</a>
	        <a 
						:href="im.fileView" 
						class="btn btn-outline-success" 
						:title="im.name + ' - ' + $store.state.texts.clanky_dokument_view" 
						target="_blank"
					>
						<i class="fas fa-file-image"></i>
					</a>
	      </div>
	    </div>
	  </div>
	</div>
</template>

<style scoped>
</style>