<?php

namespace Tests;

use App\Models\User;
use App\Models\StripeProduct;
use App\Models\Dojo;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
	protected function setUp(): void {
		parent::setUp();
		// $this->withoutExceptionHandling();
	}

	protected function signIn($user=null) {
		$user = $user ?: User::factory()->create();
		$this->be($user);
		return $this;
	}

	protected function signout() {
		Auth::logout();
	}

    protected function getSubscribeRoute($stripe_product_id,$payment_method,$dojo) {
        return "/api/subscribe?plan=".StripeProduct::find($stripe_product_id)->product_id."&payment_method=".$payment_method."&dojo_id=".$dojo->id;
    }

	protected function triggerSubscriptionWebhook() {
		$data = $this->createSubscribedDojo();
        $subscription = DB::table('subscriptions')->where(['name'=>'dojo-1'])->get()[0];
        $mock_response = $this->getStripeWebhookMock($data['dojo']['id'],StripeProduct::find(2)->product_id,$subscription->stripe_id);
        $this->post('/api/payments/webhook',[
            'is_testing'=>1,
            'mock' => $mock_response
		]);
		return $data;
	}

    protected function createSubscribedDojo($payment_method = 'pm_card_visa', $product_id = 2)
    {
        $this->addProducts();
        $dojo = Dojo::factory()->create();
        $user = User::first();
        $this->signIn($user);
        $route = $this->getSubscribeRoute($product_id,$payment_method,$dojo);
        return [
            'response' => $this->get($route),
            'dojo' => $dojo,
            'user' => $user
        ];
    }

	protected function getStripeWebhookMock($dojo_id,$product_id,$stripe_id) {
		return '{
			"id": "evt_1IKU7NLJoFktZSCLc5WDj0NX",
			"object": "event",
			"api_version": "2018-11-08",
			"created": 1613245765,
			"data": {
			  "object": {
				"id": "'.$stripe_id.'",
				"object": "subscription",
				"application_fee_percent": null,
				"billing": "charge_automatically",
				"billing_cycle_anchor": 1610305212,
				"billing_thresholds": null,
				"cancel_at": null,
				"cancel_at_period_end": false,
				"canceled_at": null,
				"collection_method": "charge_automatically",
				"created": 1610305212,
				"current_period_end": 1615402812,
				"current_period_start": 1612983612,
				"customer": "cus_IjcCwXFgQcTTEX",
				"days_until_due": null,
				"default_payment_method": null,
				"default_source": null,
				"default_tax_rates": [
				],
				"discount": null,
				"ended_at": null,
				"invoice_customer_balance_settings": {
				  "consume_applied_balance_on_void": true
				},
				"items": {
				  "object": "list",
				  "data": [
					{
					  "id": "si_IwMr0nRegVLAgn",
					  "object": "subscription_item",
					  "billing_thresholds": null,
					  "created": 1613245765,
					  "metadata": {
					  },
					  "plan": {
						"id": "'.$product_id.'",
						"object": "plan",
						"active": true,
						"aggregate_usage": null,
						"amount": 500,
						"amount_decimal": "500",
						"billing_scheme": "per_unit",
						"created": 1607026638,
						"currency": "cad",
						"interval": "month",
						"interval_count": 1,
						"livemode": false,
						"metadata": {
						},
						"nickname": null,
						"product": "prod_IVP2PcwudnBE5n",
						"tiers": null,
						"tiers_mode": null,
						"transform_usage": null,
						"trial_period_days": null,
						"usage_type": "licensed"
					  },
					  "price": {
						"id": "'.$product_id.'",
						"object": "price",
						"active": true,
						"billing_scheme": "per_unit",
						"created": 1607026638,
						"currency": "cad",
						"livemode": false,
						"lookup_key": null,
						"metadata": {
						},
						"nickname": null,
						"product": "prod_IVP2PcwudnBE5n",
						"recurring": {
						  "aggregate_usage": null,
						  "interval": "month",
						  "interval_count": 1,
						  "trial_period_days": null,
						  "usage_type": "licensed"
						},
						"tiers_mode": null,
						"transform_quantity": null,
						"type": "recurring",
						"unit_amount": 500,
						"unit_amount_decimal": "500"
					  },
					  "quantity": 1,
					  "subscription": "sub_IjcNKcmaTHLYrG",
					  "tax_rates": [
					  ]
					}
				  ],
				  "has_more": false,
				  "total_count": 1,
				  "url": "/v1/subscription_items?subscription=sub_IjcNKcmaTHLYrG"
				},
				"latest_invoice": "in_1IJNvjLJoFktZSCL1o64FhmQ",
				"livemode": false,
				"metadata": {
				  "dojo_id": "'.$dojo_id.'"
				},
				"next_pending_invoice_item_invoice": null,
				"pause_collection": null,
				"pending_invoice_item_interval": null,
				"pending_setup_intent": null,
				"pending_update": null,
				"plan": {
				  "id": "'.$product_id.'",
				  "object": "plan",
				  "active": true,
				  "aggregate_usage": null,
				  "amount": 500,
				  "amount_decimal": "500",
				  "billing_scheme": "per_unit",
				  "created": 1607026638,
				  "currency": "cad",
				  "interval": "month",
				  "interval_count": 1,
				  "livemode": false,
				  "metadata": {
				  },
				  "nickname": null,
				  "product": "prod_IVP2PcwudnBE5n",
				  "tiers": null,
				  "tiers_mode": null,
				  "transform_usage": null,
				  "trial_period_days": null,
				  "usage_type": "licensed"
				},
				"quantity": 1,
				"schedule": null,
				"start": 1613245765,
				"start_date": 1610305212,
				"status": "active",
				"tax_percent": null,
				"transfer_data": null,
				"trial_end": null,
				"trial_start": null
			  },
			  "previous_attributes": {
				"items": {
				  "data": [
					{
					  "id": "si_IjcNSvJP0qaJK3",
					  "object": "subscription_item",
					  "billing_thresholds": null,
					  "created": 1610305213,
					  "metadata": {
					  },
					  "plan": {
						"id": "price_1HuOEsLJoFktZSCL5ehIiVpv",
						"object": "plan",
						"active": true,
						"aggregate_usage": null,
						"amount": 1000,
						"amount_decimal": "1000",
						"billing_scheme": "per_unit",
						"created": 1607026638,
						"currency": "cad",
						"interval": "month",
						"interval_count": 1,
						"livemode": false,
						"metadata": {
						},
						"nickname": null,
						"product": "prod_IVP2PcwudnBE5n",
						"tiers": null,
						"tiers_mode": null,
						"transform_usage": null,
						"trial_period_days": null,
						"usage_type": "licensed"
					  },
					  "price": {
						"id": "price_1HuOEsLJoFktZSCL5ehIiVpv",
						"object": "price",
						"active": true,
						"billing_scheme": "per_unit",
						"created": 1607026638,
						"currency": "cad",
						"livemode": false,
						"lookup_key": null,
						"metadata": {
						},
						"nickname": null,
						"product": "prod_IVP2PcwudnBE5n",
						"recurring": {
						  "aggregate_usage": null,
						  "interval": "month",
						  "interval_count": 1,
						  "trial_period_days": null,
						  "usage_type": "licensed"
						},
						"tiers_mode": null,
						"transform_quantity": null,
						"type": "recurring",
						"unit_amount": 1000,
						"unit_amount_decimal": "1000"
					  },
					  "quantity": 1,
					  "subscription": "sub_IjcNKcmaTHLYrG",
					  "tax_rates": [
					  ]
					}
				  ]
				},
				"plan": {
				  "id": "price_1HuOEsLJoFktZSCL5ehIiVpv",
				  "amount": 1000,
				  "amount_decimal": "1000"
				},
				"start": 1610305212
			  }
			},
			"livemode": false,
			"pending_webhooks": 3,
			"request": {
			  "id": "req_kOIEJBWLBGoOse",
			  "idempotency_key": null
			},
			"type": "customer.subscription.updated"
		  }';
	}
}
