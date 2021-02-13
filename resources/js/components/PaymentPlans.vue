<template>
  <div class="container">
    <form
      action="/api/subscriptions"
      method="post"
      id="payment-form"
      @submit.prevent="submitPayment"
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
      <div id="card-element">
        <!-- A Stripe Element will be inserted here. -->
      </div>

      <!-- Used to display form errors. -->
      <div id="card-errors" class="text-danger" role="alert"></div>
      <input type="hidden" name="plan" v-model="plan_id" />
      <button>Submit Payment</button>
    </form>

    <div class="row">
      <div class="col-md-4">
        <h5 class="m-0">No Plan:</h5>
        <p>Your dojo will not be publicly visible</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body" :class="{'highlighted-card':plan_id==1}" @click="plan_id=1">
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
    <div class="row mt-4">
      <div class="col col-md-4 offset-md-4">
        <button class="btn btn-primary d-block m-auto">Update Plan</button>
      </div>
    </div>
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
      plan_id: null,
      plans: [
        { description: "No Plan" },
        { description: "5 CAD/month" },
        { description: "50 CAD/year" },
        { description: "10 CAD/month" },
        { description: "100 CAD/year" }
      ]
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
    this.getPaymentsIntent();
    this.setUpStripe();
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
      });
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
    },
    submitPayment() {
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
            var url = "/api/subscribe?plan=" + this.plans[this.plan_id-1].stripe_id + "&payment_method=" + "pm_card_chargeCustomerFail" + "&dojo_id=" + this.dojo_id;
            window.location = url;
            // axios.post("/api/subscribe", {
            //   plan: this.plans[this.plan_id-1].stripe_id, // stripe product/plan id
            //   payment_method: "pm_card_chargeCustomerFail", //response.setupIntent.payment_method,
            //   dojo_id: this.dojo_id
            // });
          }
        })
        .catch(error => {
          var errorElement = document.getElementById("card-errors");
          errorElement.textContent = error.message;
        });
    }
  }
};
</script>

<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
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