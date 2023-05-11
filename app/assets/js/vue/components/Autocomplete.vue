<script>
/** 
 * Component Autocomplete
 * Posledná zmena(last change): 27.02.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.0
 * 
 * Inšpirácia z: https://blog.nette.org/cs/vue-js-v-nette
 */

import axios from 'axios';

//for Tracy Debug Bar
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default {
	props: {
		link: {
			type: String,
			required: true,
		},
		inputname: { // Názov poliíčka inputu
			type: String,
			default: "searchStr"
		},
	},
	data: function () {
		return {
			searchquery: '',
			results: [],
			isOpen: false,
			isSearching: true,
			arrowCounter: -1,
		}
	},
	methods: {
		autoComplete() {
			this.$emit('autocomplete-start');
			this.results = [];
			if (this.searchquery.length > 0) {
				this.isOpen = true;
				this.isSearching = true;
			}
			if (this.searchquery.length > 2) {
				let odkaz = this.$store.state.apiPath + 'search'
				axios.get(odkaz, {params: {[this.inputname]: this.searchquery}})
							.then(response => {
								//console.log(response);
								this.results = [];
								response.data.forEach(cl => this.results.push(cl))
								this.isSearching = false; 
								//console.log(this.results);    
							})
							.catch((error) => {
								console.log(error);
							});
			}
		},
		setLink(result) {
			if (result.type == 1) {
				//return this.mylinks[1] + result.id;
				return this.link + result.id;
			} else if (result.type == 2) {
				//return this.mylinks[2] + result.id + '?first_id='+result.id_dokument;
				return this.link + result.id + '?first_id=' + result.id_dokument;
			}
		},
		onArrowDown() {
		//    if (this.arrowCounter < this.results.length - 1) {
		//        this.arrowCounter = this.arrowCounter + 1;
		//    }
		},
		onArrowUp() {
		//   if (this.arrowCounter > 0) {
		//        this.arrowCounter = this.arrowCounter - 1;
		//    }
		},
		onEnter() {
		//    this.setResult(this.results[this.arrowCounter]);
		//    this.arrowCounter = -1;
		},
		onAClick() {
			return true;
		},
		handleClickOutside(evt) {
			if (!this.$el.contains(evt.target)) {
				this.isOpen = false;
				this.arrowCounter = -1;
				this.searchquery = '';
			}
		},
	},
	mounted() {
		document.addEventListener('click', this.handleClickOutside)
	},
	destroyed() {
		document.removeEventListener('click', this.handleClickOutside)
	}
}
</script>

<template>
	<div id="autocomplete" class="autocomplete">
		<form autocomplete="off" class="my-2 my-lg-0" @submit.prevent><!--required for disable google chrome auto fill-->
			<input  type="search" 
							:placeholder="$store.state.texts.autocomplete_placeholder"
							:name="inputname"
							class="form-control mr-sm-2"
							aria-label="Search"
							v-model="searchquery"
							@input="autoComplete"
							@keydown.down="onArrowDown"
							@keydown.up="onArrowUp"
							@keydown.enter="onEnter"
			>
			<div class="autocomplete-result" v-show="isOpen">
				<ul class="list-group">
					<li class="list-group-item text-secondary" v-show="isSearching">
						<span v-show="searchquery.length > 2">
							<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
							{{ $store.state.texts.autocomplete_searching }}
						</span>
						<span v-show="searchquery.length < 3">{{ $store.state.texts.autocomplete_min_char }}</span>
					</li>
					<li class="list-group-item"
							v-for="(result, i) in results"
							:key="i"
							:class="{ 'is-active': i === arrowCounter }"
					>
						<a :href="setLink(result)" :title="result.name" @click="onAClick"> 
							{{ result.name }} 
							<div class="small" v-if="result.description != ''"><span v-html="result.description"></span></div>
						</a>
					</li>
					<li class="list-group-item text-warning" v-show="!isSearching && searchquery.length > 2 && results.length == 0">
						<span>{{ $store.state.texts.autocomplete_not_found }}</span>
					</li>
				</ul>
			</div>
		</form>
	</div>
</template>