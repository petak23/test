<script>
/** 
 * Component EditSchemaRow
 * Posledná zmena(last change): 09.03.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2
 */

export default {
	props: {
		row: {
			type: Object,
			default: {},
		},
		id_part: {
			type: Number,
			default: 0,
			required: true,
		}
	},
	data() {
		return {
			row_str: {
				max_width: null,
				schema: null,
				height: null,
				padding: null,
				widerPhotoId: null,
			},
			row_len: {
				schema: 0,
				height: 0,
				padding: 0,
				widerPhotoId: 0,
			},
			row_state: {
				max_width: null,
				schema: null,
				height: null,
				padding: null,
				widerPhotoId: null,
			},
			row_state_n: null,
			row_len_mid: 0, // Hodnota, ktorú by mali mať všetky časti row_len
			changed: false,
		}
	},
	methods: {
		setRow_str() {
			this.row_str = {
				max_width: this.row.max_width,
				schema: this.row.schema.toString(),
				height: this.row.height.toString(),
				padding: this.row.padding.toString(),
				widerPhotoId: this.row.widerPhotoId.toString(),
			}
			this.row_len = {
				schema: this.row_str.schema.split(",").length - 1,
				height: this.row_str.height.split(",").length - 1,
				padding: this.row_str.padding.split(",").length - 1,
				widerPhotoId: this.row_str.widerPhotoId.split(",").length - 1,
			}
			this.changed = false
		},
		onSaveRow(event) {
			event.preventDefault()
			if (event.srcElement.classList.contains('schema-row-save-' + this.id_part)) {
				let tmp = {
					schema: this.row_str.schema.split(",").map(x => parseInt(x)), // Aby som z textových položiek urobil čísla
					height: this.row_str.height.split(",").map(x => parseInt(x)),
					padding: this.row_str.padding.split(",").map(x => parseInt(x)),
					widerPhotoId: this.row_str.widerPhotoId.split(",").map(x => parseInt(x)),
					max_width: parseInt(this.row_str.max_width),
				}
				this.$root.$emit("schema-changed", [{ 'id_part': this.id_part, 'data': tmp}])
			}
		},
		onCancelRow(event) {
			event.preventDefault()
			if (event.srcElement.classList.contains('schema-row-cancel-'+this.id_part)) {
				this.setRow_str()
				setTimeout(() => {
					this.changed = false
					this.row_state_n = null
				}, 250)
			}
		},
		row_items_count() {
			this.row_len_mid = (this.row_len.schema + this.row_len.height + this.row_len.padding + this.row_len.widerPhotoId) / 4
		},
		/* str Testovaný reťazec
		 * with_sign Rozlíšenie či môžu byť čísla aj záporné */
		testArray(str, with_sign = false) {
			let a = str.trim().split(",")	// Odstráň medzery zo zač. a konca a rozlož do poľa
			let result = true
			// Regulárny výraz pre testovanie reťazca:
			const reg = with_sign ? /^[-]?(\d){1,4}$/ : // test: na zač. môže byť "-" a potom už len 1 až 4 čísla
															/^(\d){1,4}$/				// test: len 1 až 4 čísla
			a.map((i) => {	//Otestuj všetky položky poľa
				if (!reg.test(i)) result = false
			})
			return result ? a.length - 1 : 0	// Ak v poriadku vráť dĺžku poľa inak 0
		}
	},
	watch: {
		row(newValue, oldValue) {
			this.setRow_str()
			this.row_items_count();
		},
		'row_str.max_width': function () {
			this.changed = true
		},
		'row_str.schema': function () {
			this.changed = true
			this.row_len.schema = this.testArray(this.row_str.schema)
			this.row_state_n = this.row_len.schema == this.row_len_mid
		},
		'row_str.height': function () {
			if (this.row_str.height !== null) this.changed = true
			this.row_len.height = this.testArray(this.row_str.height)
			this.row_state_n = this.row_len.height == this.row_len_mid
		},
		'row_str.padding': function () {
			if (this.row_str.padding !== null) this.changed = true
			this.row_len.padding = this.testArray(this.row_str.padding)
			this.row_state_n = this.row_len.padding == this.row_len_mid
		},
		'row_str.widerPhotoId': function () {
			if (this.row_str.widerPhotoId !== null) this.changed = true
			this.row_len.widerPhotoId = this.testArray(this.row_str.widerPhotoId, true)
			this.row_state_n = this.row_len.widerPhotoId == this.row_len_mid
		}
	},
	mounted () {
		this.setRow_str()
		
		this.row_items_count()

		setTimeout(() => {
			this.changed = false
			this.row_state = {
				schema: null,
				height: null,
				padding: null,
				widerPhotoId: null,
				max_width: null,
			}
			this.row_state_n = null
		}, 100)
	},
}
</script>


<template>
	<b-card no-body class="mb-1">
		<b-card-header header-tag="header" class="p-1" role="tab">
			<b-button 
				block 
				v-b-toggle="'row-' + row.max_width"
				variant="secondary"
				size="sm"
			>
				Schéma pre max. šírku: {{ row.max_width }} <strong v-if="changed">- zmenené!</strong>
			</b-button>
		</b-card-header>
		<b-collapse 
			:id="'row-' + row.max_width" 
			visible accordion="my-accordion"
			role="tabpanel"
		>
			<b-card-body>
				<b-card-text class="text-dark">
					Schéma pre max. rozlíšenie [px]: <br />
					<b-form-input v-model="row_str.max_width" 
						size="sm" type="number"
					></b-form-input>
				</b-card-text>
				<b-card-text class="text-dark">
					Počet fotiek v jednotlivých riadkoch: <br />
					<b-form-input v-model="row_str.schema" 
						size="sm" type="text"
						:state="row_state_n"
					></b-form-input>
				</b-card-text>
				<b-card-text class="text-dark">
					Výška jednotlivých riadkov v px: <br />
					<b-form-input v-model="row_str.height" 
						size="sm" type="text"
						:state="row_state_n"
					></b-form-input>
				</b-card-text>
				<b-card-text class="text-dark">
					Veľkosť medzery pod daným riadkom: <br />
					<b-form-input v-model="row_str.padding" 
						size="sm" type="text"
						:state="row_state_n"
					></b-form-input>
				</b-card-text>
				<b-card-text class="text-dark">
					Poradie fotky v riadku, ktorá má byť širšia ako ostatné v riadku: <br />
					<b-form-input v-model="row_str.widerPhotoId" 
						size="sm" type="text"
						:state="row_state_n"
					></b-form-input>
				</b-card-text>
				<b-card-text>
					<button 
						type="button"
						class="btn btn-secondary btn-sm mr-1"
						:class="'schema-row-cancel-' + id_part"
						@click="onCancelRow"
						:disabled="!(changed && row_state_n != false)"
					>
						Cancel
					</button>
					<button 
						type="button"
						class="btn btn-success btn-sm"
						:class="'schema-row-save-' + id_part"
						@click="onSaveRow"
						:disabled="!(changed && row_state_n != false)	"
					>
						Ulož
					</button>
				</b-card-text>
			</b-card-body>
		</b-collapse>
	</b-card>
</template>


<style lang="scss" scoped>

</style>