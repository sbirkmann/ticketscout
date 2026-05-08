<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { loadStripe } from '@stripe/stripe-js';

const props = defineProps({
    clientSecret: String,
    stripeKey: String,
    totalAmount: Number,
    event: Object,
    orderData: Object
});

const elements = ref(null);
const stripe = ref(null);
const errorMessage = ref('');
const isProcessing = ref(false);

const paymentForm = useForm({
    orderData: props.orderData,
    paymentIntentId: ''
});

onMounted(async () => {
    stripe.value = await loadStripe(props.stripeKey);
    
    const appearance = {
        theme: 'stripe',
        variables: {
            colorPrimary: '#14b8a6', // brand-500
            colorBackground: '#ffffff',
            colorText: '#334155',
            colorDanger: '#ef4444',
            fontFamily: 'Inter, system-ui, sans-serif',
            borderRadius: '12px',
        }
    };
    
    elements.value = stripe.value.elements({ clientSecret: props.clientSecret, appearance });
    
    const paymentElement = elements.value.create('payment');
    paymentElement.mount('#payment-element');
});

const handleSubmit = async (e) => {
    e.preventDefault();
    if (!stripe.value || !elements.value) return;

    isProcessing.value = true;
    errorMessage.value = '';

    const { error, paymentIntent } = await stripe.value.confirmPayment({
        elements: elements.value,
        confirmParams: {
            return_url: window.location.origin + `/checkout/complete?event_id=${props.event.id}`,
        },
        redirect: 'if_required' // We handle redirect manually or use 'if_required' to process immediately
    });

    if (error) {
        errorMessage.value = error.message;
        isProcessing.value = false;
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
        // Payment succeeded, now create order in our backend
        paymentForm.paymentIntentId = paymentIntent.id;
        paymentForm.post(route('checkout.complete', props.event.slug));
    }
};
</script>

<template>
    <Head title="Complete Payment" />

    <div class="min-h-screen bg-surface-50 font-sans flex flex-col items-center justify-center p-6">
        
        <Link :href="route('home')" class="font-display font-black text-2xl text-surface-900 mb-8 flex items-center gap-2">
            TICKET<span class="text-brand-500">PLATFORM</span>
        </Link>

        <div class="w-full max-w-xl bg-white rounded-3xl shadow-glass border border-surface-100 overflow-hidden">
            <div class="bg-surface-100 p-8 border-b border-surface-200 text-center">
                <h2 class="font-display font-bold text-2xl text-surface-900 mb-1">Complete your payment</h2>
                <p class="text-surface-600 font-medium">Total: {{ totalAmount.toFixed(2) }} €</p>
            </div>
            
            <form @submit="handleSubmit" class="p-8">
                <div id="payment-element" class="mb-8">
                    <!-- Stripe Elements will be inserted here -->
                </div>

                <div v-if="errorMessage" class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-200">
                    {{ errorMessage }}
                </div>

                <button type="submit" :disabled="!stripe || isProcessing" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-xl shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="isProcessing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isProcessing ? 'Processing...' : `Pay ${totalAmount.toFixed(2)} €` }}
                </button>
            </form>
        </div>
        
        <div class="mt-8 text-surface-500 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            Payments are secure and encrypted
        </div>
    </div>
</template>
