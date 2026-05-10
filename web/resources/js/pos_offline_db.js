export function initDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('pos_offline_db', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('receipts')) {
                db.createObjectStore('receipts', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

export async function saveOfflineReceipt(payload) {
    const db = await initDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['receipts'], 'readwrite');
        const store = transaction.objectStore('receipts');
        const request = store.add(payload);
        
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getOfflineReceipts() {
    const db = await initDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['receipts'], 'readonly');
        const store = transaction.objectStore('receipts');
        const request = store.getAll();
        
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function deleteOfflineReceipt(id) {
    const db = await initDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['receipts'], 'readwrite');
        const store = transaction.objectStore('receipts');
        const request = store.delete(id);
        
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}
