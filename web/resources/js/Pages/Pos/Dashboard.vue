<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { saveOfflineReceipt, getOfflineReceipts, deleteOfflineReceipt } from '../../pos_offline_db.js';

const props = defineProps({
    terminal: Object,
    shift: Object,
    event: Object,
    articles: Array,
    receiptSettings: Object,
    cashiers: Array
});

// OFFLINE SYNC STATE
const isOnline = ref(navigator.onLine);
const offlineQueueCount = ref(0);
const syncingOffline = ref(false);

async function checkOfflineQueue() {
    const receipts = await getOfflineReceipts();
    offlineQueueCount.value = receipts.length;
}

async function syncOfflineQueue() {
    if (!isOnline.value || syncingOffline.value || offlineQueueCount.value === 0) return;
    
    syncingOffline.value = true;
    const receipts = await getOfflineReceipts();
    
    for (const receipt of receipts) {
        try {
            await axios.post(route('pos.checkout'), receipt);
            await deleteOfflineReceipt(receipt.id);
            offlineQueueCount.value--;
        } catch (err) {
            console.error('Failed to sync offline receipt', err);
            // stop syncing if we hit an error (e.g. backend down)
            break;
        }
    }
    syncingOffline.value = false;
}

onMounted(() => {
    window.addEventListener('online', () => {
        isOnline.value = true;
        syncOfflineQueue();
    });
    window.addEventListener('offline', () => {
        isOnline.value = false;
    });
    checkOfflineQueue();
});

// CASHIER (PIN LOGIN)
const activeCashier = ref(props.cashiers && props.cashiers.length > 0 ? null : { id: null, name: 'Admin (No Cashiers)' });
const showCashierModal = ref(props.cashiers && props.cashiers.length > 0);
const pinInput = ref('');
const pinError = ref('');

function loginCashier() {
    const cashier = props.cashiers.find(c => c.pin === pinInput.value);
    if (cashier) {
        activeCashier.value = cashier;
        showCashierModal.value = false;
        pinInput.value = '';
        pinError.value = '';
    } else {
        pinError.value = 'PIN inkorrekt';
        pinInput.value = '';
    }
}

function logoutCashier() {
    activeCashier.value = null;
    showCashierModal.value = true;
}

// CATEGORIES
const categories = computed(() => {
    const cats = [...new Set(props.articles.map(a => a.category).filter(Boolean))];
    return ['Alle', ...cats];
});
const activeCategory = ref('Alle');

const filteredArticles = computed(() => {
    if (activeCategory.value === 'Alle') return props.articles;
    return props.articles.filter(a => a.category === activeCategory.value);
});

// CART STATE
const cart = ref([]);

const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

// WALLET CUSTOMER STATE
const activeCustomer = ref(null);

function addToCart(article) {
    if (activeCustomer.value) {
        if (cartTotal.value + article.price > activeCustomer.value.wallet_balance) {
            alert(`Budget überschritten! Der Kunde hat nur noch ${Number(activeCustomer.value.wallet_balance).toFixed(2)} € Guthaben.`);
            return;
        }
    }

    const existing = cart.value.find(i => i.id === article.id);
    if (existing) {
        existing.qty++;
    } else {
        cart.value.push({
            ...article,
            qty: 1
        });
    }
}

function removeFromCart(index) {
    cart.value.splice(index, 1);
}

function clearCart() {
    cart.value = [];
}

// PAYMENT MODALS
const paymentModal = ref(null); // 'cash', 'wallet', 'card', 'success'
const processing = ref(false);
const error = ref(null);
const lastReceipt = ref(null);

// CASH
const cashGiven = ref('');
const cashReturn = computed(() => {
    const given = parseFloat(cashGiven.value);
    if (isNaN(given)) return 0;
    return given - cartTotal.value;
});

// WALLET
const scanCode = ref('');
const scanInputRef = ref(null);
const scannedTicket = ref(null);

// TIP AMOUNT
const tipAmount = ref(0);

const finalTotal = computed(() => {
    return cartTotal.value + tipAmount.value;
});

function addTip(amount) {
    tipAmount.value += amount;
}

function setTip(amount) {
    tipAmount.value = amount;
}

function openPayment(method) {
    if (cart.value.length === 0) return;
    error.value = null;
    tipAmount.value = 0; // reset tip on new payment
    paymentModal.value = method;
    if (method === 'wallet') {
        nextTick(() => scanInputRef.value?.focus());
    } else if (method === 'cash') {
        cashGiven.value = finalTotal.value.toString();
    }
}

function closePayment() {
    paymentModal.value = null;
    scannedTicket.value = null;
    scanCode.value = '';
    cashGiven.value = '';
    tipAmount.value = 0;
    error.value = null;
    terminalStatus.value = 'Standby';
}

function fetchTicket() {
    if (!scanCode.value) return;
    processing.value = true;
    error.value = null;

    axios.post(route('pos.fetch-ticket'), { code: scanCode.value })
        .then(res => {
            if (paymentModal.value === 'prescan') {
                activeCustomer.value = res.data.ticket;
                paymentModal.value = null;
                scanCode.value = '';
                return;
            }

            scannedTicket.value = res.data.ticket;
            if (scannedTicket.value.wallet_balance < finalTotal.value) {
                error.value = 'Nicht genügend Guthaben! (' + Number(scannedTicket.value.wallet_balance).toFixed(2) + ' € verfügbar)';
            }
        })
        .catch(err => {
            error.value = err.response?.data?.error || 'Ticket ungültig';
        })
        .finally(() => {
            processing.value = false;
        });
}

function submitCheckout(method) {
    if (processing.value) return;
    if (method === 'cash' && cashReturn.value < 0) {
        error.value = 'Zu wenig Bargeld gegeben.';
        return;
    }
    
    // OFFLINE MODE: Nur Barzahlung möglich
    if (!isOnline.value) {
        if (method !== 'cash') {
            error.value = 'Offline-Modus aktiv: Nur Barzahlung möglich!';
            return;
        }

        const offlineId = Date.now().toString();
        const payload = {
            id: offlineId,
            payment_method: 'cash',
            payment_reference: null,
            ticket_code: null,
            tip_amount: tipAmount.value,
            pos_cashier_id: activeCashier.value?.id,
            items: cart.value.map(i => ({
                id: i.id,
                name: i.name,
                qty: i.qty,
                price: i.price,
                tax_rate: i.tax_rate
            })),
            created_at: new Date().toISOString()
        };

        processing.value = true;
        saveOfflineReceipt(payload).then(() => {
            lastReceipt.value = {
                receipt_number: 'OFF-' + offlineId.slice(-4),
                created_at: new Date().toISOString(),
                total_gross: finalTotal.value - tipAmount.value,
                tip_amount: tipAmount.value,
                payment_method: 'cash',
                items: payload.items.map(i => ({ ...i, total: i.price * i.qty }))
            };
            offlineQueueCount.value++;
            paymentModal.value = 'success';
            clearCart();
            
            setTimeout(() => {
                printReceipt();
            }, 300);
        }).finally(() => {
            processing.value = false;
        });

        return;
    }

    // If active customer is set, we can checkout with them directly
    if (method === 'wallet') {
        const ticketToUse = activeCustomer.value || scannedTicket.value;
        if (!ticketToUse || ticketToUse.wallet_balance < finalTotal.value) {
            error.value = 'Nicht genügend Guthaben!';
            return;
        }
        
        processing.value = true;
        error.value = null;

        axios.post(route('pos.checkout'), {
            payment_method: method,
            ticket_code: ticketToUse.code,
            tip_amount: tipAmount.value,
            pos_cashier_id: activeCashier.value?.id,
            items: cart.value.map(i => ({
                id: i.id,
                qty: i.qty,
                price: i.price,
                tax_rate: i.tax_rate
            }))
        }).then(res => {
            lastReceipt.value = res.data.receipt;
            paymentModal.value = 'success';
            clearCart();
            activeCustomer.value = null; // reset
            
            setTimeout(() => {
                printReceipt();
            }, 300);

        }).catch(err => {
            error.value = err.response?.data?.error || 'Kassiervorgang fehlgeschlagen';
        }).finally(() => {
            processing.value = false;
        });
        return;
    }

    processing.value = true;
    error.value = null;

    axios.post(route('pos.checkout'), {
        payment_method: method,
        payment_reference: method === 'card' ? (window.lastPaymentIntentId || null) : null,
        ticket_code: method === 'wallet' ? scannedTicket.value.code : null,
        tip_amount: tipAmount.value,
        pos_cashier_id: activeCashier.value?.id,
        items: cart.value.map(i => ({
            id: i.id,
            qty: i.qty,
            price: i.price,
            tax_rate: i.tax_rate
        }))
    }).then(res => {
        lastReceipt.value = res.data.receipt;
        paymentModal.value = 'success';
        clearCart();
        
        setTimeout(() => {
            printReceipt();
        }, 300);

    }).catch(err => {
        error.value = err.response?.data?.error || 'Kassiervorgang fehlgeschlagen';
    }).finally(() => {
        processing.value = false;
    });
}

// STRIPE TERMINAL LOGIC
const terminalStatus = ref('Standby'); // Standby, Connecting, WaitingForCard, Processing

async function processStripeCardPayment() {
    processing.value = true;
    error.value = null;
    terminalStatus.value = 'Verbinde mit Terminal...';

    try {
        // 1. Create PaymentIntent on Backend
        const intentRes = await axios.post(route('pos.stripe.create-intent'), {
            amount: finalTotal.value,
            currency: 'eur'
        });
        const clientSecret = intentRes.data.client_secret;
        window.lastPaymentIntentId = intentRes.data.id;

        // Note: In a real production app, StripeTerminal.create() is initialized once on load.
        // And terminal.discoverReaders() connects to the device.
        // For this demo structure, we simulate the flow that would trigger collectPaymentMethod
        terminalStatus.value = 'Bitte Karte ans Terminal halten...';
        
        // --- SDK MOCK FLOW (Would be replaced by actual StripeTerminal SDK calls) ---
        // const collectResult = await terminal.collectPaymentMethod(clientSecret);
        // if (collectResult.error) throw new Error(collectResult.error.message);
        // terminalStatus.value = 'Verarbeite Zahlung...';
        // const processResult = await terminal.processPayment(collectResult.paymentIntent);
        // if (processResult.error) throw new Error(processResult.error.message);
        // --------------------------------------------------------------------------
        
        // Simulating the user tapping the card on the reader:
        setTimeout(async () => {
            terminalStatus.value = 'Verarbeite Zahlung...';
            // 2. Capture Intent on Backend
            try {
                // await axios.post(route('pos.stripe.capture-intent'), { payment_intent_id: window.lastPaymentIntentId });
                // We skip actual capture here to avoid real stripe API errors in local demo
                submitCheckout('card');
            } catch (captureErr) {
                error.value = captureErr.response?.data?.error || 'Kartenbelastung fehlgeschlagen';
                processing.value = false;
            }
        }, 2000);

    } catch (err) {
        error.value = err.response?.data?.error || err.message || 'Terminal Fehler';
        processing.value = false;
    }
}

function printReceipt() {
    window.print();
}

// CLOSE SHIFT
const showCloseShift = ref(false);
const closeShiftForm = useForm({
    ending_cash: ''
});

function openCloseShift() {
    showCloseShift.value = true;
}

function submitCloseShift() {
    closeShiftForm.post(route('pos.shift.close'));
}

function logout() {
    if (props.shift) {
        alert('Bitte erst die Kasse schließen (Z-Bon), bevor du dich abmeldest!');
        return;
    }
    router.post(route('pos.logout'));
}

// RECEIPTS & STORNO
const showReceiptsModal = ref(false);
const recentReceipts = ref([]);
const loadingReceipts = ref(false);

function openReceiptsModal() {
    showReceiptsModal.value = true;
    loadingReceipts.value = true;
    axios.get(route('pos.receipts.recent', { terminal: props.terminal.id }))
        .then(res => {
            recentReceipts.value = res.data.receipts;
        })
        .finally(() => {
            loadingReceipts.value = false;
        });
}

function refundReceipt(receipt) {
    if (!confirm(`Soll der Beleg ${receipt.receipt_number} wirklich storniert werden?`)) return;
    
    processing.value = true;
    axios.post(route('pos.receipts.refund', { receipt: receipt.id }), {
        pos_cashier_id: activeCashier.value?.id
    }).then(res => {
        alert('Storno erfolgreich!');
        // Update local list
        const idx = recentReceipts.value.findIndex(r => r.id === receipt.id);
        if (idx !== -1) {
            recentReceipts.value[idx].status = 'refunded';
        }
        // show refund receipt
        lastReceipt.value = res.data.receipt;
        setTimeout(() => printReceipt(), 300);
    }).catch(err => {
        alert(err.response?.data?.error || 'Fehler beim Stornieren');
    }).finally(() => {
        processing.value = false;
    });
}
</script>

<template>
    <Head :title="'Kasse: ' + terminal.name" />

    <div class="h-screen bg-surface-100 flex flex-col font-sans overflow-hidden">
        
        <!-- HEADER -->
        <header class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shrink-0 shadow-md relative z-10">
            <div class="flex items-center gap-6">
                <div>
                    <h1 class="font-black text-xl font-display tracking-widest uppercase">{{ terminal.name }}</h1>
                    <div class="text-surface-400 text-xs font-bold">{{ event ? event.title : 'Kein Event zugewiesen' }}</div>
                </div>
                <div v-if="shift" class="hidden md:flex items-center gap-2 bg-white/10 px-4 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full animate-pulse" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></span>
                    <span class="text-xs font-bold tracking-widest">{{ isOnline ? 'Schicht offen' : 'OFFLINE MODUS' }}</span>
                </div>
                
                <div v-if="offlineQueueCount > 0" class="flex items-center gap-2 bg-yellow-500 text-yellow-900 px-4 py-1.5 rounded-full font-bold text-xs cursor-pointer hover:bg-yellow-400 transition-colors" @click="syncOfflineQueue" title="Klicke hier um den manuellen Sync zu starten">
                    <svg v-if="syncingOffline" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span>{{ offlineQueueCount }} offline Belege{{ isOnline && !syncingOffline ? ' (Sync)' : '' }}</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div v-if="activeCashier" class="text-white font-bold bg-surface-800 px-4 py-2 rounded-xl flex items-center gap-3">
                    <span>{{ activeCashier.name }}</span>
                    <button @click="logoutCashier" class="text-surface-400 hover:text-white transition-colors" title="Kellner wechseln">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
                <button @click="openReceiptsModal" class="bg-surface-700 hover:bg-surface-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Belege
                </button>
                <button @click="openCloseShift" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors shadow-sm">
                    Kassensturz (Z-Bon)
                </button>
            </div>
        </header>

        <!-- CASHIER PIN MODAL (Blocks App) -->
        <div v-if="!activeCashier" class="absolute inset-0 z-[60] bg-surface-900 flex flex-col items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl text-center">
                <h2 class="text-2xl font-black mb-6 uppercase tracking-widest text-surface-900">Personal PIN</h2>
                <div class="flex gap-2 justify-center mb-6">
                    <div v-for="i in 4" :key="i" class="w-12 h-16 rounded-xl border-2 flex items-center justify-center text-3xl font-black font-mono transition-colors" :class="pinInput.length >= i ? 'border-brand-500 bg-brand-50 text-brand-600' : 'border-surface-200 bg-surface-50 text-transparent'">
                        *
                    </div>
                </div>
                <div v-if="pinError" class="text-red-500 font-bold mb-4 animate-pulse">{{ pinError }}</div>
                
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <button v-for="n in 9" :key="n" @click="pinInput.length < 4 ? pinInput += n : null; if(pinInput.length === 4) loginCashier();" class="bg-surface-100 hover:bg-surface-200 active:bg-surface-300 text-surface-900 text-2xl font-black font-mono py-4 rounded-xl transition-colors">{{ n }}</button>
                    <button @click="pinInput = pinInput.slice(0, -1)" class="bg-red-50 text-red-500 hover:bg-red-100 active:bg-red-200 text-xl font-bold py-4 rounded-xl transition-colors">DEL</button>
                    <button @click="pinInput.length < 4 ? pinInput += '0' : null; if(pinInput.length === 4) loginCashier();" class="bg-surface-100 hover:bg-surface-200 active:bg-surface-300 text-surface-900 text-2xl font-black font-mono py-4 rounded-xl transition-colors">0</button>
                    <button @click="loginCashier" class="bg-brand-500 text-white hover:bg-brand-600 active:bg-brand-700 font-bold text-lg py-4 rounded-xl transition-colors uppercase">OK</button>
                </div>
            </div>
        </div>

        <!-- RECEIPTS & STORNO MODAL -->
        <div v-if="showReceiptsModal" class="absolute inset-0 z-50 bg-surface-900/90 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-4xl h-[80vh] flex flex-col shadow-2xl overflow-hidden">
                <div class="p-6 bg-surface-100 border-b border-surface-200 flex items-center justify-between">
                    <h3 class="font-black text-xl text-surface-900 uppercase tracking-wider">Letzte Belege (Offene Schicht)</h3>
                    <button @click="showReceiptsModal = false" class="text-surface-400 hover:text-surface-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-6 bg-surface-50">
                    <div v-if="loadingReceipts" class="text-center py-12">Lade Belege...</div>
                    <div v-else-if="recentReceipts.length === 0" class="text-center py-12 text-surface-500 font-bold">Keine Belege in dieser Schicht vorhanden.</div>
                    <div v-else class="space-y-4">
                        <div v-for="receipt in recentReceipts" :key="receipt.id" class="bg-white border-2 border-surface-200 rounded-2xl p-4 flex items-center justify-between shadow-sm" :class="receipt.status === 'refunded' ? 'opacity-50' : ''">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="font-black text-lg font-mono">#{{ receipt.receipt_number }}</span>
                                    <span class="text-sm font-bold text-surface-500">{{ new Date(receipt.created_at).toLocaleString('de-DE') }}</span>
                                    <span v-if="receipt.status === 'refunded'" class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">STORNIERT</span>
                                </div>
                                <div class="text-sm text-surface-600 font-bold">
                                    {{ receipt.items.length }} Artikel &bull; Zahlung: {{ String(receipt.payment_method).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <div class="font-black text-xl font-mono">{{ Number(parseFloat(receipt.total_gross) + parseFloat(receipt.tip_amount || 0)).toFixed(2) }} €</div>
                                </div>
                                <button v-if="receipt.status === 'paid' && receipt.total_gross > 0" @click="refundReceipt(receipt)" :disabled="processing" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl font-bold transition-colors">
                                    Storno
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN WORKSPACE -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- LEFT: ITEMS GRID -->
            <div class="flex-1 flex flex-col overflow-hidden bg-surface-100">
                
                <!-- Wallet Prescan Banner -->
                <div v-if="activeCustomer" class="bg-brand-500 text-white px-6 py-3 flex items-center justify-between shrink-0 shadow-md z-10">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        <div>
                            <div class="font-bold text-sm opacity-80 uppercase tracking-widest">Kunde gescannt</div>
                            <div class="font-black text-xl font-mono">{{ Number(activeCustomer.wallet_balance).toFixed(2) }} € Guthaben</div>
                        </div>
                    </div>
                    <button @click="activeCustomer = null" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-sm font-bold transition-colors">Kunde abmelden</button>
                </div>
                <div v-else class="bg-surface-200 text-surface-600 px-6 py-3 flex items-center justify-between shrink-0 shadow-sm z-10">
                    <div class="font-bold text-sm uppercase tracking-widest">Schnell-Modus</div>
                    <button @click="paymentModal = 'prescan'; nextTick(() => scanInputRef?.focus())" class="bg-surface-300 hover:bg-surface-400 text-surface-800 px-4 py-2 rounded-xl text-sm font-bold transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        Kunde scannen (Prescan)
                    </button>
                </div>

                <!-- Categories Tabs -->
                <div class="bg-surface-50 border-b border-surface-200 px-6 py-3 flex overflow-x-auto gap-2 shrink-0 hide-scrollbar">
                    <button 
                        v-for="cat in categories" :key="cat"
                        @click="activeCategory = cat"
                        class="whitespace-nowrap px-6 py-2 rounded-full font-bold text-sm transition-all"
                        :class="activeCategory === cat ? 'bg-surface-900 text-white shadow-md' : 'bg-white text-surface-600 hover:bg-surface-200 border border-surface-200'"
                    >
                        {{ cat }}
                    </button>
                </div>

                <!-- Articles Grid -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div v-if="!filteredArticles || filteredArticles.length === 0" class="flex flex-col items-center justify-center h-full text-surface-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        <p class="font-bold text-xl">Keine Artikel gefunden</p>
                    </div>
                    
                    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <button 
                            v-for="article in filteredArticles" 
                            :key="article.id"
                            @click="addToCart(article)"
                            class="relative aspect-square rounded-3xl overflow-hidden flex flex-col items-center justify-center p-4 transition-transform active:scale-95 shadow-sm border border-surface-200"
                            :style="{ backgroundColor: article.color + '20' }"
                        >
                            <div class="absolute inset-0 opacity-10" :style="{ backgroundColor: article.color }"></div>
                            <h3 class="font-black text-center text-surface-900 z-10 leading-tight" :class="{'text-2xl': article.name.length < 10, 'text-lg': article.name.length >= 10}">{{ article.name }}</h3>
                            <p class="text-surface-600 font-mono font-bold mt-2 z-10">{{ Number(article.price).toFixed(2) }} €</p>
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT: SHOPPING CART -->
            <div class="w-96 bg-white border-l border-surface-200 flex flex-col shadow-xl z-20 shrink-0">
                
                <!-- Cart Header -->
                <div class="p-4 border-b border-surface-100 bg-surface-50 flex items-center justify-between">
                    <h2 class="font-black text-surface-900 tracking-wider">AKTUELLER BON</h2>
                    <button v-if="cart.length > 0" @click="clearCart" class="text-red-500 text-xs font-bold uppercase hover:text-red-700">Leeren</button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div v-if="cart.length === 0" class="text-center py-12 text-surface-400 font-bold">
                        Warenkorb ist leer
                    </div>
                    
                    <div v-for="(item, index) in cart" :key="index" class="flex items-center gap-3 bg-surface-50 p-3 rounded-2xl border border-surface-100">
                        <div class="w-10 h-10 rounded-xl bg-surface-200 flex flex-col items-center justify-center font-bold text-surface-900 shrink-0">
                            {{ item.qty }}x
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-surface-900 truncate">{{ item.name }}</h4>
                            <p class="text-xs text-surface-500 font-mono">{{ Number(item.price).toFixed(2) }} € / Stk.</p>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-surface-900 font-mono">{{ Number(item.price * item.qty).toFixed(2) }} €</div>
                        </div>
                        <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 p-1 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Total & Checkout -->
                <div class="p-6 bg-surface-900 text-white rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)]">
                    <div class="flex items-end justify-between mb-6">
                        <span class="text-surface-400 font-bold uppercase tracking-widest text-sm">Gesamtsumme</span>
                        <span class="text-5xl font-black font-mono tracking-tighter">{{ Number(cartTotal).toFixed(2) }} €</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button @click="openPayment('wallet')" :disabled="cart.length === 0" class="col-span-2 bg-brand-500 text-white font-black py-4 rounded-xl hover:bg-brand-600 active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 uppercase tracking-wider shadow-lg shadow-brand-500/30 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            Guthaben Scannen
                        </button>
                        <button @click="openPayment('cash')" :disabled="cart.length === 0" class="bg-surface-700 text-white font-black py-3 rounded-xl hover:bg-surface-600 active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 uppercase tracking-wider text-sm flex flex-col items-center">
                            Barzahlung
                        </button>
                        <button @click="openPayment('card')" :disabled="cart.length === 0" class="bg-surface-700 text-white font-black py-3 rounded-xl hover:bg-surface-600 active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 uppercase tracking-wider text-sm flex flex-col items-center">
                            Kartenzahlung
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- PAYMENT MODALS OVERLAY -->
        <div v-if="paymentModal" class="fixed inset-0 bg-surface-900/90 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            
            <!-- PRESCAN WALLET -->
            <div v-if="paymentModal === 'prescan'" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">
                <div class="p-6 bg-surface-100 border-b border-surface-200 flex items-center justify-between">
                    <h3 class="font-black text-xl text-surface-900 uppercase tracking-wider">Kunde Scannen</h3>
                    <button @click="closePayment" class="text-surface-400 hover:text-surface-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-8">
                    <div class="mb-4 text-surface-500 font-bold text-center">Bitte Festival-Bändchen oder Ticket-QR scannen:</div>
                    <form @submit.prevent="fetchTicket" class="relative">
                        <input 
                            ref="scanInputRef"
                            v-model="scanCode" 
                            type="text" 
                            class="w-full bg-surface-900 border-0 text-white rounded-2xl py-6 px-6 text-center text-2xl tracking-widest focus:ring-brand-500 font-mono" 
                            placeholder="SCANNEN..."
                            :disabled="processing"
                        >
                    </form>
                    <div v-if="error" class="text-red-500 bg-red-50 p-4 rounded-xl font-bold mt-4 text-center">{{ error }}</div>
                </div>
            </div>

            <!-- CASH PAYMENT -->
            <div v-if="paymentModal === 'cash'" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">
                <div class="p-6 bg-surface-100 border-b border-surface-200 flex items-center justify-between">
                    <h3 class="font-black text-xl text-surface-900 uppercase tracking-wider">Barzahlung</h3>
                    <button @click="closePayment" class="text-surface-400 hover:text-surface-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-8">
                    <div class="flex justify-between items-center mb-8 border-b border-surface-200 pb-6">
                        <span class="text-surface-500 font-bold uppercase tracking-wider">Zu zahlen</span>
                        <span class="text-4xl font-black font-mono">{{ Number(cartTotal).toFixed(2) }} €</span>
                    </div>

                    <div class="mb-8">
                        <label class="block text-surface-500 font-bold uppercase tracking-wider text-sm mb-2">Gegeben</label>
                        <div class="relative">
                            <input v-model="cashGiven" type="number" step="0.01" min="0" class="w-full bg-surface-100 border-2 border-surface-300 rounded-2xl py-4 pl-4 pr-16 text-3xl font-mono focus:border-brand-500 focus:ring-brand-500">
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-2xl font-bold text-surface-400">€</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-8 bg-surface-50 p-4 rounded-2xl">
                        <span class="text-surface-500 font-bold uppercase tracking-wider">Rückgeld</span>
                        <span class="text-3xl font-black font-mono" :class="cashReturn >= 0 ? 'text-green-600' : 'text-red-500'">{{ Number(cashReturn).toFixed(2) }} €</span>
                    </div>

                    <div v-if="error" class="text-red-500 font-bold mb-4 text-center">{{ error }}</div>

                    <button @click="submitCheckout('cash')" :disabled="processing || cashReturn < 0" class="w-full bg-surface-900 text-white font-black py-5 rounded-2xl text-xl hover:bg-surface-800 transition-colors uppercase tracking-widest disabled:opacity-50">
                        {{ processing ? '...' : 'Beleg buchen' }}
                    </button>
                </div>
            </div>

            <!-- WALLET PAYMENT -->
            <div v-if="paymentModal === 'wallet'" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">
                <div class="p-6 bg-surface-100 border-b border-surface-200 flex items-center justify-between">
                    <h3 class="font-black text-xl text-surface-900 uppercase tracking-wider">Guthaben (Wallet)</h3>
                    <button @click="closePayment" class="text-surface-400 hover:text-surface-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-8">
                    <!-- TIP SELECTION -->
                    <div class="mb-6 bg-surface-50 p-4 rounded-xl">
                        <div class="text-sm font-bold text-surface-500 uppercase tracking-widest mb-3 text-center">Trinkgeld hinzufügen</div>
                        <div class="flex gap-2 justify-center">
                            <button @click="setTip(0)" :class="tipAmount === 0 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">Ohne</button>
                            <button @click="setTip(1)" :class="tipAmount === 1 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">+1 €</button>
                            <button @click="setTip(2)" :class="tipAmount === 2 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">+2 €</button>
                            <button @click="setTip(Math.ceil(cartTotal) - cartTotal)" :class="tipAmount > 0 && tipAmount !== 1 && tipAmount !== 2 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">Aufrunden</button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-6 border-t border-surface-200 pt-4">
                        <span class="text-surface-500 font-bold uppercase tracking-wider">Zu zahlen</span>
                        <span class="text-3xl font-black font-mono">{{ Number(finalTotal).toFixed(2) }} €</span>
                    </div>

                    <form v-if="!scannedTicket && !activeCustomer" @submit.prevent="fetchTicket" class="relative">
                        <input 
                            ref="scanInputRef"
                            v-model="scanCode" 
                            type="text" 
                            class="w-full bg-surface-900 border-0 text-white rounded-2xl py-6 px-6 text-center text-2xl tracking-widest focus:ring-brand-500 font-mono" 
                            placeholder="TICKET SCANNEN..."
                            :disabled="processing"
                        >
                    </form>

                    <div v-else class="bg-brand-50 border border-brand-200 p-6 rounded-2xl mb-6 text-center">
                        <p class="text-brand-800 font-bold mb-2">{{ activeCustomer ? 'Aktiver Kunde' : 'Ticket erkannt' }}</p>
                        <p class="text-4xl font-black text-brand-600 font-mono mb-2">{{ Number((activeCustomer || scannedTicket).wallet_balance).toFixed(2) }} €</p>
                        <p class="text-sm text-brand-700">Verfügbares Guthaben</p>
                    </div>

                    <div v-if="error" class="text-red-500 bg-red-50 p-4 rounded-xl font-bold mt-4 text-center">{{ error }}</div>

                    <button v-if="scannedTicket || activeCustomer" @click="submitCheckout('wallet')" :disabled="processing || (activeCustomer || scannedTicket).wallet_balance < finalTotal" class="w-full bg-brand-500 text-white font-black py-5 rounded-2xl text-xl hover:bg-brand-600 transition-colors uppercase tracking-widest disabled:opacity-50 mt-6 shadow-lg shadow-brand-500/30">
                        {{ processing ? '...' : 'Jetzt abbuchen' }}
                    </button>
                </div>
            </div>

            <!-- CARD PAYMENT (Stripe Terminal) -->
            <div v-if="paymentModal === 'card'" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">
                <div class="p-6 bg-surface-100 border-b border-surface-200 flex items-center justify-between">
                    <h3 class="font-black text-xl text-surface-900 uppercase tracking-wider">Kartenzahlung</h3>
                    <button @click="closePayment" class="text-surface-400 hover:text-surface-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-12 text-center">
                    
                    <!-- TIP SELECTION -->
                    <div v-if="terminalStatus === 'Standby'" class="mb-6 bg-surface-50 p-4 rounded-xl">
                        <div class="text-sm font-bold text-surface-500 uppercase tracking-widest mb-3 text-center">Trinkgeld hinzufügen</div>
                        <div class="flex gap-2 justify-center">
                            <button @click="setTip(0)" :class="tipAmount === 0 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">Ohne</button>
                            <button @click="setTip(1)" :class="tipAmount === 1 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">+1 €</button>
                            <button @click="setTip(2)" :class="tipAmount === 2 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">+2 €</button>
                            <button @click="setTip(Math.ceil(cartTotal) - cartTotal)" :class="tipAmount > 0 && tipAmount !== 1 && tipAmount !== 2 ? 'bg-brand-500 text-white' : 'bg-white border-2 border-surface-200 text-surface-600'" class="px-4 py-2 rounded-lg font-bold transition">Aufrunden</button>
                        </div>
                    </div>

                    <svg v-if="terminalStatus !== 'Standby'" xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-brand-500 mb-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    
                    <p v-if="terminalStatus !== 'Standby'" class="font-bold text-xl text-surface-700 mb-2">Bitte Karte ans Terminal halten</p>
                    <p class="text-surface-500 text-sm mb-8">Gesamtsumme: <span class="font-mono font-bold text-2xl text-black">{{ Number(finalTotal).toFixed(2) }} €</span></p>
                    
                    <button v-if="terminalStatus === 'Standby'" @click="processStripeCardPayment" :disabled="processing" class="bg-brand-500 text-white font-black px-8 py-4 rounded-2xl hover:bg-brand-600 transition-colors uppercase tracking-widest disabled:opacity-50">
                        Zahlung am Terminal starten
                    </button>
                    <div v-else class="text-brand-600 font-bold text-lg mt-4 animate-pulse">
                        {{ terminalStatus }}
                    </div>
                    
                    <div v-if="error" class="text-red-500 font-bold mt-4">{{ error }}</div>
                </div>
            </div>

            <!-- SUCCESS -->
            <div v-if="paymentModal === 'success'" class="bg-green-500 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl text-white text-center p-12 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                <div class="relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h2 class="text-4xl font-black mb-2 tracking-widest uppercase">Bezahlt!</h2>
                    <p class="text-green-100 font-mono text-xl mb-8">Beleg #{{ lastReceipt?.receipt_number }}</p>
                    <button @click="closePayment" class="bg-white text-green-600 font-black px-10 py-5 rounded-2xl text-xl hover:bg-green-50 transition-colors uppercase tracking-widest shadow-xl">
                        Neue Bestellung
                    </button>
                </div>
            </div>

        </div>

        <!-- CLOSE SHIFT MODAL -->
        <div v-if="showCloseShift" class="fixed inset-0 bg-surface-900/90 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
                <div class="p-6 bg-surface-900 text-white text-center">
                    <h3 class="font-black text-2xl uppercase tracking-wider">Kasse schließen</h3>
                </div>
                <div class="p-8">
                    <form @submit.prevent="submitCloseShift">
                        <div class="mb-6">
                            <label class="block text-surface-500 font-bold uppercase tracking-wider text-sm mb-2">Gezähltes Bargeld (Ist-Bestand)</label>
                            <div class="relative">
                                <input v-model="closeShiftForm.ending_cash" type="number" step="0.01" min="0" class="w-full bg-surface-100 border-2 border-surface-300 rounded-2xl py-4 pl-4 pr-16 text-3xl font-mono focus:border-brand-500 focus:ring-brand-500" placeholder="0.00" required>
                                <span class="absolute right-6 top-1/2 -translate-y-1/2 text-2xl font-bold text-surface-400">€</span>
                            </div>
                            <p class="text-xs text-surface-500 mt-2">Bitte zähle das gesamte Bargeld in der Schublade.</p>
                        </div>
                        <div class="flex gap-4">
                            <button type="button" @click="showCloseShift = false" class="flex-1 bg-surface-200 text-surface-700 font-bold py-4 rounded-xl hover:bg-surface-300 transition-colors">Abbrechen</button>
                            <button type="submit" :disabled="closeShiftForm.processing" class="flex-1 bg-red-500 text-white font-black py-4 rounded-xl hover:bg-red-600 transition-colors uppercase">Z-Bon erstellen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- PRINT RECEIPT LAYOUT -->
        <div v-if="lastReceipt" class="hidden print:block absolute inset-0 bg-white z-[100] text-black font-mono text-xs p-4 w-[80mm] mx-auto">
            <div class="text-center mb-4">
                <div class="font-bold text-lg mb-2" v-if="receiptSettings?.company_name">{{ receiptSettings.company_name }}</div>
                <div class="whitespace-pre-line">{{ receiptSettings?.header }}</div>
            </div>

            <div class="border-b border-black border-dashed pb-2 mb-2 text-center">
                <div class="font-bold">Kaufbeleg</div>
                <div>Beleg-Nr: {{ lastReceipt.receipt_number }}</div>
                <div>Datum: {{ new Date(lastReceipt.created_at).toLocaleString('de-DE') }}</div>
                <div>Kasse: {{ terminal.name }}</div>
            </div>

            <table class="w-full mb-2">
                <tbody>
                    <tr v-for="item in lastReceipt.items" :key="item.id">
                        <td class="align-top py-1">{{ item.quantity }}x</td>
                        <td class="align-top py-1 px-2">{{ item.name }}</td>
                        <td class="align-top py-1 text-right">{{ Number(item.total).toFixed(2) }} €</td>
                    </tr>
                </tbody>
            </table>

            <div class="border-t border-black border-dashed pt-2 mb-2">
                <div class="flex justify-between font-bold text-sm">
                    <span>Gesamt (Brutto)</span>
                    <span>{{ Number(lastReceipt.total_gross).toFixed(2) }} €</span>
                </div>
            </div>

            <div class="mb-4 text-xs">
                <div class="flex justify-between" v-for="(details, rate) in lastReceipt.tax_details" :key="rate">
                    <span>MwSt. {{ rate }}%</span>
                    <span>{{ Number(details.tax).toFixed(2) }} €</span>
                </div>
            </div>

            <div class="mb-4">
                <div v-if="lastReceipt.tip_amount > 0" class="flex justify-between font-bold text-sm mb-2">
                    <span>Trinkgeld</span>
                    <span>{{ Number(lastReceipt.tip_amount).toFixed(2) }} €</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Gegeben: {{ String(lastReceipt.payment_method).toUpperCase() }}</span>
                    <span>{{ Number(parseFloat(lastReceipt.total_gross) + parseFloat(lastReceipt.tip_amount || 0)).toFixed(2) }} €</span>
                </div>
                <div v-if="paymentModal === 'cash'" class="flex justify-between">
                    <span>Erhalten</span>
                    <span>{{ Number(cashGiven).toFixed(2) }} €</span>
                </div>
                <div v-if="paymentModal === 'cash'" class="flex justify-between">
                    <span>Zurück</span>
                    <span>{{ Number(cashReturn).toFixed(2) }} €</span>
                </div>
            </div>

            <div class="text-center mt-6 pt-4 border-t border-black border-dashed whitespace-pre-line">
                {{ receiptSettings?.footer }}
            </div>
        </div>

    </div>
</template>
