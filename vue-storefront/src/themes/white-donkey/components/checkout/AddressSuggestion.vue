<template>
  <div class="address-suggestion relative col-xs-12 mb35">
    <base-input
      @input="sendQuery"
      :value="street"
      type="text"
      placeholder="Address"
      autocomplete="new-password"
      helper="Street address or PO box"
      @blur="suggestionOnBlur"
      v-on:keyup.up="handleKeyUp(-1)"
      v-on:keyup.down="handleKeyUp(1)"
      v-on:keyup.esc="cleanSuggestions()"
      v-on:keyup.delete="cleanSuggestions()"
      v-on:keyup.enter="selectByKey()"
      :validations="[
        {
          condition: $v.street.$error && !$v.street.required,
          text: 'Field is required'
        }
      ]"
      required
    />
    <ul class="address-suggestion__address-list" v-if="suggestions.length > 0">
      <li v-for="(s, index) in suggestions"
        :key="s.id + '-' + index"
        @mousedown="$event.preventDefault()"
        @click="selectByKey()"
        @mousemove="selectedIndex = index"
        :class="{'selected': selectedIndex === index}"
      >
        <div class="w-100">
          <span>{{ s.address }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import BaseInput from 'theme/components/form/BaseInput'
import { required } from 'vuelidate/lib/validators'

export default {
  name: 'AddressSuggestion',
  props: {
    street: {type: String, default: ''}
  },
  model: {
    prop: 'street',
    event: 'change',
  },
  data() {
    return {
      selectedIndex: null,
      suggestions: [],
      updateSuggestions: false,
      loading: true,
      selectedAddress: null,
    }
  },
  components: {
    BaseInput,
  },
  methods: {
    suggestionOnBlur() {
      this.$v.street.$touch();
      this.$emit('isValidAddress', this.$v.street.$invalid)
      this.cleanSuggestions()
    },
    sendQuery(query) {
      this.$emit('change', query);
      if(query.length > 4 && this.loading === false) {
        this.loading = true;
        this.suggestions = [];
        this.$store.dispatch('themeCart/addressSuggestion', { query }).then((r)=>{
          if (r.result && r.result.length > 0) {
            this.suggestions = r.result;
          }
        }).finally(()=>this.loading=false);
      }
    },
    selectAddress(suggest) {
      if (suggest.id) {
        this.loading = true;
        this.$store.dispatch('themeCart/addressDetails', {id: suggest.id}).then((r) => {
          if (r.result) {
            this.$emit('selectedAddress', r.result);
          }
        }).finally( () => {
          this.loading = false;
          this.suggestions = [];
        });
      }
    },
    cleanSuggestions() {
      this.suggestions = [];
      this.selectedIndex = null;
    },
    selectByKey() {
      if (this.selectedIndex !== null) {
        this.selectAddress(this.suggestions[this.selectedIndex]);
      }
    },
    handleKeyUp(delta) {
      this.selectedIndex = this.selectedIndex === null ? 0 : this.selectedIndex + delta;
      this.selectedIndex = this.selectedIndex < 0 ? this.suggestions.length -1 : this.selectedIndex;
      this.selectedIndex = this.selectedIndex < this.suggestions.length ? this.selectedIndex : 0;
    }
  },
  mounted() {
    this.$nextTick(()=>{
      this.loading = false;
    });
  },
  validations: {
    street: {
      required
    }
  }
}
</script>

<style lang="scss">
  .address-suggestion__address-list {
    position: absolute;
    margin-top: 5px;
    padding: 0;
    border: 1px solid #ccc;
    background: #fff;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    z-index: 40;
    li {
      padding: 6px 12px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
      list-style-type: none;
      &.selected {
        background-color: #ffeecc;
      }
    }
  }
</style>
