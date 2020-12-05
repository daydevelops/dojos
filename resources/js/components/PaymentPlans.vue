<template>
  <div class="container">
    <form
      action="/api/subscriptions"
      method="post"
      id="payment-form"
      @submit.prevent="submitPayment"
    >
      <div class="form-group">
        <label for="card-holder-name"></label>
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
      <div id="card-errors" role="alert"></div>
      <input type="hidden" name="plan" v-model="selected_id" />
      <button>Submit Payment</button>
    </form>
    <div class="row">
      <div class="col-md-4">
        <h5 class="m-0">No Plan:</h5>
        <p>Your dojo will not be publicly visible</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div
            class="card-body"
            :class="{'highlighted-card':selected_id==0}"
            @click="selected_id=0"
          >
            <h5 class="card-title" v-text="plans[0].price"></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-md-4">
        <h5 class="m-0">Standard Plans:</h5>
        <p>A standard plan allows you to publicly advertise your dojo's listing</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div
            class="card-body"
            :class="{'highlighted-card':selected_id==1}"
            @click="selected_id=1"
          >
            <h5 class="card-title" v-text="plans[1].price"></h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div
            class="card-body"
            :class="{'highlighted-card':selected_id==2}"
            @click="selected_id=2"
          >
            <h5 class="card-title" v-text="plans[2].price"></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-md-4">
        <h5 class="m-0">Premium Plans:</h5>
        <p>Purchasing a premium plan will highlight your dojo's listing to attract more attention.</p>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div
            class="card-body"
            :class="{'highlighted-card':selected_id==3}"
            @click="selected_id=3"
          >
            <h5 class="card-title" v-text="plans[3].price"></h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div
            class="card-body"
            :class="{'highlighted-card':selected_id==4}"
            @click="selected_id=4"
          >
            <h5 class="card-title" v-text="plans[4].price"></h5>
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
  props: ["plan_id", "dojo_id"],
  data() {
    return {
      stripeKey: "pk_test_uAJ3ZPwpRHr52pYcFM4EBDQg",
      stripe: "",
      elements: "",
      card: "",
      setup_intents: {},
      selected_id: 0,
      plans: [
        {
          stripe_id: null,
          price: "Free"
        },
        {
          stripe_id: "price_1HuOEsLJoFktZSCLnWRHetER",
          price: "5 CAD/month"
        },
        {
          stripe_id: "price_1HuOEsLJoFktZSCLrl93uWwZ",
          price: "50 CAD/year"
        },
        {
          stripe_id: "price_1HuOEsLJoFktZSCL5ehIiVpv",
          price: "10 CAD/month"
        },
        {
          stripe_id: "price_1HuOEsLJoFktZSCLtrn8qBdr",
          price: "100 CAD/year"
        }
      ]
    };
  },
  mounted() {
    this.selected_id = this.plan_id;
    this.getPaymentsIntent();
    this.setUpStripe();
  },
  methods: {
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

      // Custom styling can be passed to options when creating an Element.
      // (Note that this demo uses a wider set of styles than the guide below.)
      var style = {
        base: {
          color: "#32325d",
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSmoothing: "antialiased",
          fontSize: "16px",
          "::placeholder": {
            color: "#aab7c4"
          }
        },
        invalid: {
          color: "#fa755a",
          iconColor: "#fa755a"
        }
      };

      // Create an instance of the card Element.
      this.card = this.elements.create("card", { style: style });

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
            // Send the payment method to your server.
            axios.post("/api/subscriptions", {
              plan: this.plans[this.selected_id].stripe_id,
              payment_method: response.setupIntent.payment_method
            });
        })
        .catch(error => {
            var errorElement = document.getElementById("card-errors");
            errorElement.textContent = error.message;
        });

      //   this.stripe.createToken(this.card).then(result => {
      //     if (result.error) {
      //       // Inform the user if there was an error.
      //       var errorElement = document.getElementById("card-errors");
      //       errorElement.textContent = result.error.message;
      //     } else {
      //       // Send the token to your server.
      //       axios.post("/api/subscriptions", {
      //         plan: this.plans[this.selected_id].stripe_id,
      //         stripeToken: result.token.id
      //       });
      //     }
      //   });
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