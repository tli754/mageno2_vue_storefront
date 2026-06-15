<template>
  <div class="row pl20 adderss-test">
    <address-suggestion
      text="Address line 1"
      @selectedAddress="updateSelectedAddress"
      helperText="Company, street address or PO box"
      required
    />
    <base-input
      class="col-xs-12 col-sm-6 mb10"
      type="text"
      name="suburb"
      placeholder="Suburb (optional)"
      v-model.trim="suburb"
      autocomplete="new-password"
    />
    <base-input
      class="col-xs-12 col-sm-6 mb10"
      type="text"
      name="city"
      placeholder="City"
      v-model.trim="city"
      autocomplete="new-password"
    />

    <base-input
      class="col-xs-12 col-sm-6 mb10"
      type="text"
      name="postcode"
      placeholder="Postcode"
      v-model.trim="postcode"
      autocomplete="new-password"
    />

  </div>
</template>

<script>
import BaseInput from 'theme/components/core/blocks/Form/BaseInput'
import AddressSuggestion from "./checkout/AddressSuggestion";

export default {
  name: 'AddressTest',
  data() {
    return {
      suggestAddresses: [],
      addressLoading: true,
      street: '',
      suburb: '',
      city: '',
      postcode: '',
    }
  },
  components: {
    BaseInput,
    AddressSuggestion,
  },
  methods: {
    updateSelectedAddress(address) {
      this.street1 = address.street;
      this.suburb = address.suburb;
      this.city = address.city;
      this.postcode = address.postcode;
    }
  },
  // mounted() {
  //   this.$nextTick(()=>{
  //     this.addressLoading = false;
  //   });
  // }
}
</script>
