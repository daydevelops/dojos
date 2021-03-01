<template>
  <div class="container">
    <a href="/billing" target="_blank">Billing [ONLY SHOW IF THEY ARE A STRIPE CUSTOMER]</a> 
    <table>
        <tr v-for="invoice in invoices" :key="invoice.id">
            <td v-text="invoice.date"></td>
            <td v-text="invoice.total"></td>
            <td><a :href="'/api/payments/invoice/'+invoice.id">Download</a></td>
        </tr>
</table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      invoices: {}
    };
  },
  mounted() {
    axios.get("/api/payments/invoice").then(response => {
      this.invoices = response.data;
    });
  },
  methods: {
    
  }
};

</script>
