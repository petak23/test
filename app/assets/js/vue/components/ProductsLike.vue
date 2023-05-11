<script>
/**
 * Komponenta pre vypísanie obľúbených produktov.
 * Posledna zmena 22.02.2023
 *
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 * 
 * @description https://www.npmjs.com/package/vue-session
 */

export default {
	props: {
		filePath: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			/*{
					id_product: 0,
					id_article: 0,
					source: "",
					name: "",
				}*/
			liked: [], 
		}
	},
	methods: {
		getFromSession() {
			let spom = this.$session.getAll()
			this.liked = []
			for (const [key, value] of Object.entries(spom)) {
				//console.log(`${key}: ${value}`);
				if (key.startsWith("like")) {
					this.liked.push(JSON.parse(value))
				}
			}
		},
		delAll(e) {
			this.$session.clear()
			this.liked = []
			this.$root.$emit("product-like-del-all", [])
		},
		delOne(id) {
			this.$session.remove('like-' + id)
			this.getFromSession()
		}
	},
	mounted () {
		this.$session.start()

		this.getFromSession()

		this.$root.$on("product-like", liked => {			
			if (!this.$session.has('like-' + liked[0].id_product))
				this.$session.set('like-' + liked[0].id_product, JSON.stringify(liked[0]))
			else 
				this.$session.remove('like-' + liked[0].id_product)
			this.getFromSession()
		});
	}
}
</script>

<template>
	<div class="liked" v-if="liked.length > 0">
		<div class="btn-group dropup">
			<button 
				type="button" 
				class="btn btn-lg btn-success dropdown-toggle rounded-pill" 
				data-toggle="dropdown" aria-expanded="false"
			>
				<i class="fa-regular fa-heart my-heart"></i>
				<span class="badge badge-pill badge-warning">
					{{ liked.length }}
				</span>
			</button>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="#" @click.prevent="delAll">Vymaž všetky položky</a>
				<span
					v-for="i in liked"
					:key="i.id_product" 
					class="dropdown-item-text d-flex justify-content-between"
				>
					<a :href="filePath + 'clanky/' + i.id_article + '/?first_id=' + i.id_product">
						<b-avatar variant="info" :src="filePath + i.source"></b-avatar>
						{{ i.name }}
					</a>
					<b-button variant="light" @click.prevent="delOne(i.id_product)">
						<i class="fa-regular fa-trash-can text-danger"></i>
					</b-button>
				</span>
			</div>
		</div>
	</div>
</template>

<style scoped>
.liked {
  font-size: 0.9rem;
	position: fixed;
	left: 2em;
	bottom: 2em;
  max-width: 50vw;
  z-index: 2000;
	border-width: .25rem;
}

/*.my-heart {
	margin-left: 1ex;
	margin-bottom: 1ex;
	margin-top: 1ex;
}*/
.badge {
	position: absolute !important;
	top: -10px !important;
	right: -10px;
}
.dropdown-menu {
	min-width: 22rem;
}
</style>