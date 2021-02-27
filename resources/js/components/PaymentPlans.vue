<template>
  <div class="container">
    <h3 class="text-center">Subscription</h3>
    <div class="row">
      <div class="col-md-4">
        <h5 class="m-0">No Plan:</h5>
        <p>Your dojo will not be publicly visible</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body" :class="{'highlighted-card':plan_id==1}" @click="getFreePlan">
            <h5 class="card-title" v-text="plans[0].description"></h5>
          </div>
        </div>
      </div>
    </div>

    <!-- Standard Plans -->
    <div class="row mt-4">
      <div class="col-md-4">
        <h5 class="m-0">Standard Plans:</h5>
        <p>A standard plan allows you to publicly advertise your dojo's listing</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body" :class="{'highlighted-card':plan_id==2}" @click="plan_id=2">
            <h5 class="card-title" v-text="plans[1].description"></h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body" :class="{'highlighted-card':plan_id==3}" @click="plan_id=3">
            <h5 class="card-title" v-text="plans[2].description"></h5>
          </div>
        </div>
      </div>
    </div>

    <!-- Premium Plans -->
    <div class="row mt-4">
      <div class="col-md-4">
        <h5 class="m-0">Premium Plans:</h5>
        <p>Purchasing a premium plan will highlight your dojo's listing to attract more attention.</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body" :class="{'highlighted-card':plan_id==4}" @click="plan_id=4">
            <h5 class="card-title" v-text="plans[3].description"></h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body" :class="{'highlighted-card':plan_id==5}" @click="plan_id=5">
            <h5 class="card-title" v-text="plans[4].description"></h5>
          </div>
        </div>
      </div>
    </div>

    <h3 class='text-center mt-4'>Payment Options<a href="/billing" target="_blank"><i class="fas fa-edit m-2 text-success"></i></a></h3>
    <!-- Payment Methods -->
    <div class="row mb-2" v-for="pm in payment_methods" :key="pm.id">
      <div class="col col-sm-4 offset-sm-4">
        <div class="card">
          <div class="card-body p-2 px-4 text-center" :class="{'highlighted-card':selected_payment_method==pm.id}" @click="updatePaymentMethod(pm.id)">
            <h5 class="card-text d-inline mr-4" v-text="pm.brand"></h5><p class='d-inline'><i v-text="'**** **** **** ' + pm.last4"></i></p>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-2">
      <div class="col col-sm-4 offset-sm-4">
        <div class="card">
          <div class="card-body p-2 px-4 text-center" :class="{'highlighted-card':selected_payment_method=='new'}" @click="updatePaymentMethod('new')">
            <h5 class="card-text d-inline mr-4">Add New Payment Card</h5>
          </div>
        </div>
      </div>
    </div>
    <button v-if="!showing_card_form" class="btn btn-primary m-auto d-block" id="card-button" @click="submitPayment()">Update Subscription</button>

    <form
      action="/api/subscriptions"
      method="post"
      id="payment-form"
      class="mt-4"
      :class="{'d-none':!showing_card_form}"
      @submit.prevent="submitStripe"
    >
      <div class="form-group">
        <label for="card-holder-name">Card Holder Name</label>
        <input
          type="text"
          class="form-control"
          name="card-holder-name"
          id="card-holder-name"
          aria-describedby="helpId"
          placeholder
        />
      </div>

      <label for="card-element">Credit or debit card</label>
      <div id="card-element" class="mb-4">
        <!-- A Stripe Element will be inserted here. -->
      </div>

      <!-- Used to display form errors. -->
      <div id="card-errors" class="text-danger" role="alert"></div>
      <input type="hidden" name="plan" v-model="plan_id" />
      <button class="btn btn-primary m-auto d-block" id="card-button" :data-secret="this.setup_intents.client_secret">Update Subscription</button>
    </form>
    <AreYouSureModal
      id="free-plan-selected-modal"
      action="downgrade (your dojo will not be advertised)"
      btncolor="danger"
      btntext="Downgrade"
      v-on:confirm="downgradeToFreePlan"
    ></AreYouSureModal>
  </div>
</template>


<script>
export default {
  props: ["dojo_id"],
  data() {
    return {
      stripeKey: "pk_test_uAJ3ZPwpRHr52pYcFM4EBDQg",
      stripe: "",
      elements: "",
      card: "",
      setup_intents: {},
      payment_methods: {},
      selected_payment_method: null,
      showing_card_form: false,
      stripe_initialized: false,
      adding_new_card: false,
      plan_id: null,
      plans: [
        { description: "No Plan" },
        { description: "5 CAD/month" },
        { description: "50 CAD/year" },
        { description: "10 CAD/month" },
        { description: "100 CAD/year" }
      ],
    };
  },
  watch: {
    // fetch current plan id for this dojo once the dojo id is given to us
    dojo_id: function(val) {
      if (this.plan_id == null) {
        axios.get("/api/dojos/" + this.dojo_id + "/plan").then(response => {
          this.plan_id = response.data;
        });
      }
    }
  },
  mounted() {
    this.getStripePlans();
    this.getPaymentMethods();
  },
  methods: {
    // et the details for the available plans
    getStripePlans() {
      axios.get("/api/subscribe/plans").then(response => {
        this.plans = response.data;
      });
    },
    // get the users stripe information ready
    getPaymentsIntent() {
      axios.get("/api/payments/getIntents").then(response => {
        this.setup_intents = response.data;
        this.setUpStripe();
      });
    },
    // get the users payment methods
    getPaymentMethods() {
      axios.get("/api/payments/getMethods").then(response => {
        this.payment_methods = response.data;
        if (this.payment_methods.length > 0) {
          this.selected_payment_method = this.payment_methods[0].id;
        }
      });
    },
    updatePaymentMethod(pm_id) {
      if (pm_id == 'new') {
        // show the card form if not already shown
        if (!this.showing_card_form) {
          this.showing_card_form = true;
          if (!this.stripe_initialized) {
            this.getPaymentsIntent();
          }
        }
        this.selected_payment_method = 'new';
      } else {
        this.selected_payment_method = pm_id;
          this.showing_card_form = false;
      }



    },
    // user has selected free plan, show are you sure modal
    getFreePlan() {
      this.plan_id = 1;
      $("#free-plan-selected-modal").modal("show");
    },
    // user has confirmed to downgrade to free plan
    downgradeToFreePlan() {
      window.location =
        "/api/subscribe?plan=" +
        this.plans[this.plan_id - 1].product_id +
        "&payment_method=NA&dojo_id=" +
        this.dojo_id;
    },
    setUpStripe() {
      // Create a Stripe client.
      this.stripe = Stripe(this.stripeKey);

      // Create an instance of Elements.
      this.elements = this.stripe.elements();

      // Create an instance of the card Element.
      this.card = this.elements.create("card");

      // Add an instance of the card Element into the `card-element` <div>.
      this.card.mount("#card-element");
      // Handle real-time validation errors from the card Element.
      this.card.on("change", function(event) {
        var displayError = document.getElementById("card-errors");
        if (event.error) {
          displayError.textContent = event.error.message;
        } else {
          displayError.textContent = "";
        }
      });

      this.stripe_initialized = true;
    },
    submitStripe() {
      const cardHolderName = document.getElementById("card-holder-name");
      const clientSecret = this.setup_intents.client_secret;
      this.stripe
        .confirmCardSetup(clientSecret, {
          payment_method: {
            card: this.card,
            billing_details: { name: cardHolderName.value }
          }
        })
        .then(response => {
          if (response.error) {
            var errorElement = document.getElementById("card-errors");
            errorElement.textContent += response.error.message;
          } else {
            // Send the payment to the server
            this.selected_payment_method = response.setupIntent.payment_method;
            this.adding_new_card = true;
            this.submitPayment();
          }
        })
        .catch(error => {
          var errorElement = document.getElementById("card-errors");
          errorElement.textContent = error.message;
        });
    },
    submitPayment() {
      var new_card = this.adding_new_card ? "1" : "0";
      var url = "/api/subscribe?plan=" + this.plans[this.plan_id-1].product_id + "&payment_method=" + this.selected_payment_method + "&dojo_id=" + this.dojo_id + "&new_card=" + new_card;
      window.location = url;
    }
  }
};
</script>

<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */

 #payment-form {
   max-width:500px;
   margin: auto;
 }

.StripeElement {
  box-sizing: border-box;

  height: 40px;

  padding: 10px 12px;

  border: 1px solid transparent;
  border-radius: 4px;
  background-color: white;

  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}

.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
  border-color: #fa755a;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}
</style>