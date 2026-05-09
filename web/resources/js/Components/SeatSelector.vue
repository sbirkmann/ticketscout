<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    seatingPlan: Object,
    ticketCategories: Array,
    cartItems: Array, // [{ type, id, name, qty, price, total }]
});

const emit = defineEmits(['seats-selected', 'cart-updated']);

const selectedSeats = ref([]);
const showMismatchPopup = ref(false);
const mismatchInfo = ref(null);
const pendingSeat = ref(null);

// Reservation logic
const reservationToken = ref(localStorage.getItem('seat_reservation_token'));
const expiresAt = ref(null);
const countdown = ref('');
let timer = null;

function startTimer(expiry) {
    if (timer) clearInterval(timer);
    expiresAt.value = new Date(expiry);
    
    timer = setInterval(() => {
        const now = new Date();
        const diff = expiresAt.value - now;
        if (diff <= 0) {
            clearInterval(timer);
            countdown.value = 'Abgelaufen';
            alert('Deine Sitzplatz-Reservierung ist abgelaufen. Bitte wähle deine Plätze erneut.');
            selectedSeats.value = [];
            emit('seats-selected', []);
            rebuildCart([]);
            return;
        }
        const min = Math.floor(diff / 1000 / 60);
        const sec = Math.floor((diff / 1000) % 60);
        countdown.value = `${min}:${sec.toString().padStart(2, '0')}`;
    }, 1000);
}

async function syncReservation(seats) {
    if (seats.length === 0) {
        if (reservationToken.value) {
            await fetch('/api/seats/release', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ token: reservationToken.value })
            });
        }
        expiresAt.value = null;
        clearInterval(timer);
        countdown.value = '';
        return;
    }

    try {
        const resp = await fetch('/api/seats/reserve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify({
                seat_ids: seats.map(s => s.id),
                token: reservationToken.value
            })
        });
        const data = await resp.json();
        if (data.success) {
            reservationToken.value = data.token;
            localStorage.setItem('seat_reservation_token', data.token);
            startTimer(data.expires_at);
        } else if (resp.status === 409) {
            alert(data.message || 'Einige Plätze sind nicht mehr verfügbar.');
            // Refresh logic would be good here, or just remove the taken seats
        }
    } catch (e) {
        console.error("Reservation error", e);
    }
}

// Tickets ordered before entering seat selector
const orderedTickets = computed(() => (props.cartItems ?? []).filter(i => i.type === 'ticket'));
const orderedCount = computed(() => orderedTickets.value.reduce((s, i) => s + i.qty, 0));
const orderedCategory = computed(() => orderedTickets.value[0]?.name ?? null);

const categoryColors = {
    'Standard':      '#6366f1',
    'VIP':           '#f59e0b',
    'Golden Circle': '#10b981',
    'Stehplatz':     '#8b5cf6',
    'default':       '#64748b',
};

function getCategoryColor(cat) {
    return categoryColors[cat] ?? categoryColors.default;
}

function isSuggestedCategory(seat) {
    return orderedCategory.value && seat.category === orderedCategory.value;
}

function getSeatClasses(seat) {
    if (seat.status === 'sold')     return 'bg-red-200 text-red-400 cursor-not-allowed border-red-300 opacity-60';
    if (seat.status === 'reserved') return 'bg-yellow-200 text-yellow-500 cursor-not-allowed border-yellow-300 opacity-70';
    if (isSelected(seat))           return 'ring-2 ring-brand-500 scale-110 shadow-lg cursor-pointer border-brand-400';
    if (isSuggestedCategory(seat))  return 'hover:scale-110 hover:shadow-md cursor-pointer ring-2 ring-offset-1 ring-green-400 shadow-sm';
    return 'hover:scale-105 hover:shadow-sm cursor-pointer opacity-60 border-surface-200';
}

function isSelected(seat) {
    return selectedSeats.value.some(s => s.id === seat.id);
}

function toggleSeat(seat) {
    if (seat.status !== 'available') return;

    // Deselect always allowed
    if (isSelected(seat)) {
        const next = selectedSeats.value.filter(s => s.id !== seat.id);
        selectedSeats.value = next;
        emit('seats-selected', next);
        rebuildCart(next);
        syncReservation(next);
        return;
    }

    const newCount = selectedSeats.value.length + 1;
    const categoryMismatch = orderedCategory.value && seat.category !== orderedCategory.value;
    const countExceeded = orderedCount.value > 0 && newCount > orderedCount.value;

    if ((categoryMismatch || countExceeded) && orderedCount.value > 0) {
        pendingSeat.value = seat;
        mismatchInfo.value = {
            categoryMismatch,
            countExceeded,
            orderedCategory: orderedCategory.value,
            orderedCount: orderedCount.value,
            seatCategory: seat.category,
            newCount,
        };
        showMismatchPopup.value = true;
        return;
    }

    if (checkOrphan(seat)) {
        alert("Bitte lasse keine einzelnen Plätze frei. Wähle deine Plätze so, dass keine Lücken entstehen.");
        return;
    }

    applySelect(seat);
}

function checkOrphan(seat) {
    // Find row and seat index
    let rowObj = null;
    let seatIdx = -1;
    for (const r of props.seatingPlan.rows) {
        const idx = r.seats.findIndex(s => s.id === seat.id);
        if (idx !== -1) {
            rowObj = r;
            seatIdx = idx;
            break;
        }
    }
    if (!rowObj) return false;

    const rowSeats = rowObj.seats;
    
    // Helper to check if a seat is "taken" (sold, reserved, or currently selected)
    const isTaken = (s) => s.status !== 'available' || isSelected(s) || s.id === seat.id;

    // Check if selecting this seat leaves an orphan to the left
    if (seatIdx > 1) {
        const left1 = rowSeats[seatIdx - 1];
        const left2 = rowSeats[seatIdx - 2];
        // If seat-1 is available and seat-2 is taken, seat-1 becomes an orphan
        if (left1.status === 'available' && !isSelected(left1) && isTaken(left2)) {
            return true;
        }
    } else if (seatIdx === 1) {
        const left1 = rowSeats[0];
        // If seat is at index 1 and index 0 is available, index 0 becomes an orphan at the row start
        if (left1.status === 'available' && !isSelected(left1)) {
            return true;
        }
    }

    // Check if selecting this seat leaves an orphan to the right
    if (seatIdx < rowSeats.length - 2) {
        const right1 = rowSeats[seatIdx + 1];
        const right2 = rowSeats[seatIdx + 2];
        if (right1.status === 'available' && !isSelected(right1) && isTaken(right2)) {
            return true;
        }
    } else if (seatIdx === rowSeats.length - 2) {
        const right1 = rowSeats[rowSeats.length - 1];
        if (right1.status === 'available' && !isSelected(right1)) {
            return true;
        }
    }

    return false;
}

function applySelect(seat) {
    const next = [...selectedSeats.value, seat];
    selectedSeats.value = next;
    emit('seats-selected', next);
    rebuildCart(next);
    syncReservation(next);
}

function confirmMismatch() {
    if (pendingSeat.value) applySelect(pendingSeat.value);
    pendingSeat.value = null;
    showMismatchPopup.value = false;
}

function cancelMismatch() {
    pendingSeat.value = null;
    showMismatchPopup.value = false;
}

function rebuildCart(seats) {
    const grouped = {};
    seats.forEach(seat => {
        const cat = seat.category;
        const price = parseFloat(seat.price_override ?? props.ticketCategories?.find(c => c.name === cat)?.price ?? 0);
        if (!grouped[cat]) {
            const origItem = (props.cartItems ?? []).find(i => i.name === cat && i.type === 'ticket');
            grouped[cat] = { type: 'ticket', id: origItem?.id ?? null, name: cat, price, qty: 0, total: 0 };
        }
        grouped[cat].qty++;
        grouped[cat].total = Math.round(grouped[cat].price * grouped[cat].qty * 100) / 100;
    });
    emit('cart-updated', Object.values(grouped));
}

const totalPrice = computed(() =>
    selectedSeats.value.reduce((sum, seat) => {
        const p = parseFloat(seat.price_override ?? props.ticketCategories?.find(c => c.name === seat.category)?.price ?? 0);
        return sum + p;
    }, 0)
);

function formatMoney(v) {
    return parseFloat(v || 0).toFixed(2).replace('.', ',') + ' €';
}
</script>

<template>
    <div class="select-none">
        <!-- Stage -->
        <div class="flex items-center justify-center mb-8">
            <div class="bg-surface-800 text-white rounded-xl px-10 py-3 text-sm font-bold tracking-widest uppercase shadow-md">
                🎭 Bühne
            </div>
        </div>

        <!-- Category hint -->
        <div v-if="orderedCategory" class="mb-5 flex items-center gap-3 bg-white dark:bg-surface-800 border-l-4 border-green-500 rounded-r-xl px-5 py-3 shadow-sm">
            <div class="w-4 h-4 rounded-full shrink-0 ring-2 ring-green-400" :style="{ backgroundColor: getCategoryColor(orderedCategory) }"></div>
            <span class="text-sm text-surface-800 dark:text-surface-200">
                Du hast <strong class="text-surface-900 dark:text-white">{{ orderedCount }}× {{ orderedCategory }}</strong> gebucht –
                die passenden Sitze sind <strong class="text-green-700 dark:text-green-400">grün umrandet</strong>.
            </span>
        </div>

        <!-- Grid -->
        <div class="overflow-x-auto pb-4">
            <div class="min-w-max mx-auto space-y-3">
                <div v-for="row in seatingPlan.rows" :key="row.id" class="flex items-center gap-3">
                    <div class="w-14 text-xs font-bold text-surface-400 text-right shrink-0">
                        {{ row.label }}
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-for="seat in row.seats"
                            :key="seat.id"
                            @click="toggleSeat(seat)"
                            :disabled="seat.status !== 'available'"
                            :title="`${seat.label} – ${seat.category} (${formatMoney(seat.price_override ?? ticketCategories?.find(c => c.name === seat.category)?.price ?? 0)})`"
                            class="w-11 h-11 rounded-t-full rounded-b-md text-xs font-bold border-2 transition-all duration-150 flex items-center justify-center"
                            :class="getSeatClasses(seat)"
                            :style="seat.status === 'available' && !isSelected(seat)
                                ? { backgroundColor: getCategoryColor(seat.category) + '22', borderColor: getCategoryColor(seat.category) + '55', color: getCategoryColor(seat.category) }
                                : isSelected(seat)
                                    ? { backgroundColor: getCategoryColor(seat.category), borderColor: getCategoryColor(seat.category), color: '#fff' }
                                    : {}"
                        >
                            {{ seat.seat_number }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 justify-center mt-6 text-xs text-surface-600 dark:text-surface-400">
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-t-full rounded-b-sm bg-surface-50 border-2 ring-2 ring-green-400 border-green-300"></div>
                Deine Kategorie
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-t-full rounded-b-sm bg-indigo-500 border-2 border-indigo-500"></div> Ausgewählt
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-t-full rounded-b-sm bg-red-200 border-2 border-red-300 opacity-60"></div> Vergeben
            </div>
            <div v-for="cat in ticketCategories" :key="cat.name" class="flex items-center gap-1.5">
                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: getCategoryColor(cat.name) }"></div>
                {{ cat.name }} ({{ cat.price }} €)
            </div>
        </div>

        <!-- Summary -->
        <div v-if="selectedSeats.length > 0" class="mt-6 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-2xl overflow-hidden shadow-sm">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3 bg-surface-50 dark:bg-surface-700/50 border-b border-surface-200 dark:border-surface-700">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-surface-900 dark:text-white">
                        {{ selectedSeats.length }} Platz{{ selectedSeats.length > 1 ? 'e' : '' }} gewählt
                    </span>
                    <div v-if="countdown" class="flex items-center gap-1.5 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-bold border border-amber-200 dark:border-amber-800 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Reserviert: {{ countdown }}
                    </div>
                </div>
                <span class="text-xl font-black text-brand-600 dark:text-brand-400">{{ formatMoney(totalPrice) }}</span>
            </div>
            <!-- Per-seat rows -->
            <div class="divide-y divide-surface-100 dark:divide-surface-700">
                <div v-for="seat in selectedSeats" :key="seat.id"
                    class="flex items-center justify-between px-5 py-2.5 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: getCategoryColor(seat.category) }"></div>
                        <span class="font-bold text-surface-800 dark:text-surface-200">{{ seat.label }}</span>
                        <span class="text-surface-400 dark:text-surface-500">· {{ seat.category }}</span>
                    </div>
                    <span class="font-semibold text-surface-700 dark:text-surface-300">
                        {{ formatMoney(seat.price_override ?? ticketCategories?.find(c => c.name === seat.category)?.price ?? 0) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Mismatch Popup -->
        <Teleport to="body">
            <div v-if="showMismatchPopup" class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelMismatch"></div>
                <div class="relative bg-white dark:bg-surface-900 rounded-3xl shadow-2xl p-8 max-w-md w-full z-10">
                    <div class="text-5xl text-center mb-4">⚠️</div>
                    <h3 class="text-xl font-black text-surface-900 dark:text-white text-center mb-4">Preis wird angepasst</h3>

                    <div class="space-y-3 text-sm mb-6">
                        <div v-if="mismatchInfo.categoryMismatch" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 rounded-xl p-4">
                            <p class="font-bold text-amber-800 dark:text-amber-400 mb-1">Andere Kategorie</p>
                            <p class="text-amber-700 dark:text-amber-300">
                                Dieser Platz ist <strong>{{ mismatchInfo.seatCategory }}</strong>,
                                du hattest <strong>{{ mismatchInfo.orderedCategory }}</strong> gebucht.
                                Der Preis wird entsprechend aktualisiert.
                            </p>
                        </div>
                        <div v-if="mismatchInfo.countExceeded" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 rounded-xl p-4">
                            <p class="font-bold text-blue-800 dark:text-blue-400 mb-1">Mehr Tickets</p>
                            <p class="text-blue-700 dark:text-blue-300">
                                Du wählst {{ mismatchInfo.newCount }} Plätze, hattest aber nur
                                {{ mismatchInfo.orderedCount }} gebucht. Ein Ticket wird hinzugefügt.
                            </p>
                        </div>
                        <p class="text-center text-surface-400 text-xs">Der Warenkorb wird automatisch aktualisiert.</p>
                    </div>

                    <div class="flex gap-3">
                        <button @click="cancelMismatch"
                            class="flex-1 bg-surface-100 hover:bg-surface-200 dark:bg-surface-800 text-surface-700 dark:text-surface-300 py-3 rounded-xl font-bold transition-colors">
                            Abbrechen
                        </button>
                        <button @click="confirmMismatch"
                            class="flex-1 bg-brand-500 hover:bg-brand-600 text-white py-3 rounded-xl font-bold transition-colors shadow-sm">
                            Trotzdem wählen
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
