<template xmlns="http://www.w3.org/1999/html">
  <div class="personal-details">
    <div class="row pl20">
      <div class="col-xs-1 col-sm-2 col-md-1">
        <div
          class="number-circle lh35 cl-white brdr-circle align-center weight-700"
          :class="{ 'bg-cl-th-accent' : isActive || isFilled, 'bg-cl-tertiary' : !isFilled && !isActive }"
        >
          1
        </div>
      </div>
      <div class="col-xs-11 col-sm-9 col-md-11">
        <div class="row mb15">
          <div class="col-xs-12 col-md-7" :class="{ 'cl-bg-tertiary' : !isFilled && !isActive }">
            <h3 :class="{'m0': true, 'mb30': isActive}">
              Personal Details
            </h3>
          </div>
          <div class="col-xs-12 col-md-5 pr30">
            <div class="lh30 flex end-lg" v-if="isFilled && !isActive">
              <a href="#" class="cl-tertiary flex" @click.prevent="edit">
                <span class="pr5">
                  Edit personal details
                </span>
                <i class="material-icons cl-tertiary">edit</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-11 col-sm-9 col-md-10">
        <div class="row">
          <base-input
            class="col-xs-12 col-md-6 mb25"
            type="text"
            :autofocus="true"
            name="first-name"
            placeholder="First name"
            v-model.trim="personalDetails.firstName"
            @blur="$v.personalDetails.firstName.$touch()"
            autocomplete="first-name"
            :validations="[
              {
                condition: $v.personalDetails.firstName.$error && !$v.personalDetails.firstName.required,
                text: 'Field is required'
              },
              {
                condition: !$v.personalDetails.firstName.minLength,
                text: 'Name must have at least 2 letters.'
              }
            ]"
            required
          />
          <base-input
            class="col-xs-12 col-md-6 mb25"
            type="text"
            name="last-name"
            placeholder="Last name"
            v-model.trim="personalDetails.lastName"
            @blur="$v.personalDetails.lastName.$touch()"
            autocomplete="last-name"
            :validations="[{
              condition: $v.personalDetails.lastName.$error && !$v.personalDetails.lastName.required,
              text: 'Field is required'
            }]"
            required
          />
          <base-input
            class="col-xs-12 mb25"
            type="email"
            name="email-address"
            placeholder="Email address"
            v-model="personalDetails.emailAddress"
            @blur="$v.personalDetails.emailAddress.$touch()"
            autocomplete="email"
            @keyup.enter="sendDataToCheckout"
            :validations="[
              {
                condition: $v.personalDetails.emailAddress.$error && !$v.personalDetails.emailAddress.required,
                text: 'Field is required'
              },
              {
                condition: !$v.personalDetails.emailAddress.email && $v.personalDetails.emailAddress.$error,
                text: 'Please provide valid e-mail address.'
              }
            ]"
            required
          />
        </div>
      </div>
    </div>
    <div class="row" v-show="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-11 col-sm-9 col-md-10">
        <div class="row my30">
          <div class="col-xs-12 col-xl-7 px20 button-container">
            <button-full
              data-testid="personalDetailsSubmit"
              @click.native="sendDataToCheckout"
              :disabled="$v.personalDetails.$invalid"
            >
              Continue to address
            </button-full>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="!isActive && isFilled">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-xs-12">
            <span>{{ personalDetails.firstName }} {{ personalDetails.lastName }}, </span>
            <span>{{ personalDetails.emailAddress }}</span>
            <span class="h5 cl-bg-tertiary">(We will send you details regarding the order)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { required, minLength, email } from 'vuelidate/lib/validators'
import BaseInput from 'theme/components/form/BaseInput'
import ButtonFull from 'theme/components/theme/ButtonFull'

export default {
  name: 'PersonalDetails',
  components: {
    ButtonFull,
    BaseInput
  },
  props: {
    isActive: {
      type: Boolean,
      required: true
    }
  },
  data () {
    return {
      isFilled: false,
      personalDetails: this.$store.state.checkout.personalDetails,
    }
  },
  methods: {
    sendDataToCheckout () {
      this.$store.dispatch('checkout/savePersonalDetails', this.personalDetails)
      this.$emit('updateActiveSection', 'shipping')
      this.isFilled = true
    },
    edit () {
      if (this.isFilled) {
        this.$emit('updateActiveSection', 'personalDetails')
      }
    },
  },
  mounted () {
    this.personalDetails = this.$store.state.checkout.personalDetails
  },
  validations: {
    personalDetails: {
      firstName: {
        required,
        minLength: minLength(2)
      },
      lastName: {
        required
      },
      emailAddress: {
        required,
        email
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.link {
  text-decoration: underline;
}

.login-prompt {
  @media (min-width: 1200px) {
    margin-top: 30px;
  }
}
</style>
