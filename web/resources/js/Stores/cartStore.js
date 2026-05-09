import { reactive, watch } from 'vue';

const savedCart = localStorage.getItem('ts24_cart');
export const cartStore = reactive({
    items: savedCart ? JSON.parse(savedCart) : [],
    event: localStorage.getItem('ts24_cart_event') ? JSON.parse(localStorage.getItem('ts24_cart_event')) : null,
    
    addItem(item, eventData) {
        if (this.event && this.event.id !== eventData.id) {
            // This case should be handled by a confirmation before calling addItem
            this.clearCart();
        }
        this.event = eventData;
        const existing = this.items.find(i => i.id === item.id && i.type === item.type && i.name === item.name);
        if (existing) {
            existing.qty += item.qty;
            existing.total = Math.round(existing.price * existing.qty * 100) / 100;
        } else {
            this.items.push(item);
        }
        this.save();
    },

    setItems(items, eventData) {
        this.items = items;
        this.event = eventData;
        this.save();
    },

    clearCart() {
        this.items = [];
        this.event = null;
        this.save();
    },

    save() {
        localStorage.setItem('ts24_cart', JSON.stringify(this.items));
        localStorage.setItem('ts24_cart_event', JSON.stringify(this.event));
    }
});

// Watch for changes and save (as a backup to manual save)
watch(() => cartStore.items, () => cartStore.save(), { deep: true });
