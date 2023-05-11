<script>
/** 
 * Component ShowArticle
 * Posledná zmena(last change): 16.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.0
 * 
 */
import EditTexts from "./Article/EditTexts.vue"
import EditTitle from "./Article/EditTitle.vue"
import axios from 'axios'

export default {
	components: {
		EditTexts,
		EditTitle,
	},
	props: {
		filesPath: { // Adresár k súborom vrátanekoncového lomítka
			type: String,
			//required: true
		}, 
		edit_enabled: String,
		link: String,
		//link_to_admin: String,
		article_hlavicka: String,
		article_avatar_view_in: String,
		id_hlavne_menu_lang: {
			type: String,
			required: true,
		},
		view_h1: {	// Povolenie zobrazenia nadpisu H1
			type: String,
			default: "0", //Povolenie zobrazenia je "1"
		},
		text_class: {	// Doplnkový class pre textové pole
			type: String,
			default: "",
		},
		container_id: { // Id hlavného kontajnera
			type: String,
		}
	},
	data() {
		return {
			article: null,
		}
	},
	methods: {
				// Načítanie aktuálneho článku
		getArticle() {
			let odkaz = this.$store.state.apiPath + 'menu/getonemenuarticle/' + this.id_hlavne_menu_lang
			axios.get(odkaz)
				.then(response => {
					this.article = response.data
					//console.log(response.data)
				})
				.catch((error) => {
					console.log(odkaz);
					console.log(error);
				});
		},
	},
	computed: {
		modalNameTitle() {
			return 'EditTitleModalID' + this.id_hlavne_menu_lang
		},
		modalNameText() {
			return 'EditTextModalID' + this.id_hlavne_menu_lang 
		}
	},
	created: function () {
		this.$root.$on("reload-article-" + this.id_hlavne_menu_lang, data => {
			this.article = data[0]
		})
	},
	mounted () {
		this.getArticle()
	},
}
</script>

<template>
	<div
		:id="container_id"
		class="sa-container"
		v-if="article != null"
	>
		<!--div class="page-header">
			<div  
				class="col-sm-12 col-md-3"
				v-if="(parseInt(article_avatar_view_in) & 2) && $store.state.article.avatar != null"
			>
				<img  :src="filesPath + 'www/' + $store.state.article.avatar" 
							alt="Title image"
							class="img-fluid"
				>
			</div>
			<edit-title
				:edit_enabled="parseInt(edit_enabled)"
				:link="link"
				:link_to_admin="link_to_admin"
				:article_hlavicka="article_hlavicka"
			></edit-title>
		</div -->
		<h1 v-if="view_h1 == '1'">
			{{ article.view_name }}
			<small v-if="article.h1part2 != null" class="ml-2">
				{{ article.h1part2 }}
			</small>
		</h1>
		<div v-if="edit_enabled == 1"
				class="btn-group btn-group-sm editable" 
				role="group"
		>
			<b-button
				v-if="view_h1 == '1'"
				variant="outline-warning"
				size="sm"
				v-b-modal="modalNameTitle"
				:title="$store.state.texts.base_edit_title"
			>
				<i class="fas fa-pen"></i>
			</b-button>
			<b-button 
				variant="outline-warning"
				size="sm"
				:title="$store.state.texts.base_edit_texts"
				v-b-modal="modalNameText"
			>
				<i class="fa-solid fa-file-lines"></i>
			</b-button>
			<edit-title
				:button_prefix="'buttonTitleID' + id_hlavne_menu_lang"
				:article="article"
				:modal_name="modalNameTitle"
			></edit-title>
			<edit-texts
				:button_prefix="'buttonTextID' + id_hlavne_menu_lang"
				:article="article"
				:modal_name="modalNameText"
			>
			</edit-texts>
		</div>
		<span 
			class="popis"
			:class="text_class"
			v-html="article.text_c">
		</span>
	</div>
</template>

<style scoped>
	/*.title-info {
		border-right: 1px solid #ddd;
		margin-right: .5ex;
		padding-right: .25ex;
	}
	.title-info:last-child {
		border-right: 0;
	}*/
	.sa-container{
		position: relative;
	}
	.editable {
		position: absolute;
		top: 0;
		right: 0;
	}
</style>