const express = require('express');
const cors = require('cors');
const sqlite3 = require('sqlite3').verbose();
const axios = require('axios');
const fs = require('fs');
const path = require('path');
const { app, BrowserWindow, Menu, Tray, nativeImage } = require('electron');

const CONFIG_FILE = path.join(app.getPath('userData'), 'hub-config.json');
const DB_FILE = path.join(app.getPath('userData'), 'transactions.sqlite');

let config = {
    backendUrl: 'http://localhost:8000/api/pos',
    hubKey: '',
    hubName: 'Main Bar Hub',
    port: 8080,
    printers: [] // Array of { id, name, ip, port }
};

if (fs.existsSync(CONFIG_FILE)) {
    try {
        const data = fs.readFileSync(CONFIG_FILE, 'utf-8');
        config = { ...config, ...JSON.parse(data) };
    } catch (e) {}
}

function saveConfig() {
    fs.writeFileSync(CONFIG_FILE, JSON.stringify(config, null, 2));
}

// Init SQLite
const db = new sqlite3.Database(DB_FILE);
db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        backend_id INTEGER UNIQUE,
        receipt_number TEXT,
        total_gross REAL,
        payment_method TEXT,
        created_at TEXT
    )`);
});

const expressApp = express();
expressApp.use(cors());
expressApp.use(express.json());

// Express API
expressApp.get('/api/config', (req, res) => res.json(config));
expressApp.post('/api/config', (req, res) => {
    config = { ...config, ...req.body };
    saveConfig();
    res.json({ success: true });
});

expressApp.get('/api/transactions', (req, res) => {
    db.all("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 100", [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ transactions: rows });
    });
});

expressApp.post('/api/printers', (req, res) => {
    const printer = { id: Date.now(), name: req.body.name, ip: req.body.ip, port: req.body.port || 9100 };
    config.printers.push(printer);
    saveConfig();
    res.json(printer);
});

expressApp.delete('/api/printers/:id', (req, res) => {
    config.printers = config.printers.filter(p => p.id != req.params.id);
    saveConfig();
    res.json({ success: true });
});

expressApp.get('/', (req, res) => {
    res.send(`
    <html>
        <head>
            <title>Ticketscout POS Local Hub</title>
            <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        </head>
        <body class="bg-gray-100 p-8 font-sans">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-black mb-6">POS Local Hub Dashboard</h1>
                
                <div class="grid grid-cols-2 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h2 class="text-xl font-bold mb-4">Einstellungen</h2>
                        <input id="backendUrl" value="${config.backendUrl}" class="border p-2 w-full mb-2 rounded" placeholder="Backend URL">
                        <input id="hubKey" value="${config.hubKey}" class="border p-2 w-full mb-2 rounded" placeholder="Hub Access Key">
                        <input id="hubName" value="${config.hubName}" class="border p-2 w-full mb-4 rounded" placeholder="Location Name">
                        <button onclick="saveSettings()" class="bg-blue-600 text-white px-4 py-2 rounded">Speichern</button>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow">
                        <h2 class="text-xl font-bold mb-4">Lokale IP-Drucker</h2>
                        <ul id="printerList" class="mb-4">
                            ${config.printers.map(p => `<li class="flex justify-between items-center py-2 border-b"><span>${p.name} (${p.ip}:${p.port})</span> <button onclick="delPrinter(${p.id})" class="text-red-500">X</button></li>`).join('')}
                        </ul>
                        <div class="flex gap-2">
                            <input id="pName" placeholder="Name" class="border p-2 w-1/3 rounded">
                            <input id="pIp" placeholder="192.168.1.100" class="border p-2 w-1/3 rounded">
                            <button onclick="addPrinter()" class="bg-green-600 text-white px-4 py-2 rounded w-1/3">Hinzufügen</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow mt-8">
                    <h2 class="text-xl font-bold mb-4">Lokale Transaktionen (Offline-Backup)</h2>
                    <table class="w-full text-left">
                        <thead><tr class="bg-gray-100"><th class="p-2">Beleg</th><th class="p-2">Zeit</th><th class="p-2">Zahlart</th><th class="p-2">Summe</th></tr></thead>
                        <tbody id="txList"></tbody>
                    </table>
                </div>
            </div>

            <script>
                function saveSettings() {
                    fetch('/api/config', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            backendUrl: document.getElementById('backendUrl').value,
                            hubKey: document.getElementById('hubKey').value,
                            hubName: document.getElementById('hubName').value
                        })
                    }).then(() => alert('Gespeichert!'));
                }

                function addPrinter() {
                    fetch('/api/printers', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            name: document.getElementById('pName').value,
                            ip: document.getElementById('pIp').value,
                            port: 9100
                        })
                    }).then(() => location.reload());
                }

                function delPrinter(id) {
                    fetch('/api/printers/'+id, { method: 'DELETE' }).then(() => location.reload());
                }

                fetch('/api/transactions').then(r => r.json()).then(data => {
                    const tbody = document.getElementById('txList');
                    tbody.innerHTML = data.transactions.map(tx => \`<tr class="border-b">
                        <td class="p-2">\${tx.receipt_number}</td>
                        <td class="p-2">\${tx.created_at}</td>
                        <td class="p-2 uppercase">\${tx.payment_method}</td>
                        <td class="p-2 font-mono">\${tx.total_gross.toFixed(2)} €</td>
                    </tr>\`).join('');
                });
            </script>
        </body>
    </html>
    `);
});

expressApp.listen(config.port, () => {
    console.log(`Local Hub Server running on http://localhost:${config.port}`);
});

// Polling for backend transactions (Sync)
setInterval(async () => {
    if (!config.backendUrl || !config.hubKey) return;
    try {
        // Find latest transaction we have
        db.get("SELECT backend_id FROM transactions ORDER BY backend_id DESC LIMIT 1", async (err, row) => {
            const lastId = row ? row.backend_id : 0;
            
            const res = await axios.get(`${config.backendUrl}/hub/sync`, {
                params: { last_id: lastId },
                headers: { 'X-HUB-KEY': config.hubKey }
            });

            const newTx = res.data.transactions;
            if (newTx && newTx.length > 0) {
                const stmt = db.prepare("INSERT INTO transactions (backend_id, receipt_number, total_gross, payment_method, created_at) VALUES (?, ?, ?, ?, ?)");
                for (const tx of newTx) {
                    stmt.run([tx.id, tx.receipt_number, tx.total_gross, tx.payment_method, tx.created_at]);
                }
                stmt.finalize();
                console.log(`Synced ${newTx.length} new transactions.`);
            }
        });
    } catch (e) {
        console.error('Sync failed', e.message);
    }
}, 10000);

let tray = null;
app.whenReady().then(() => {
    const icon = nativeImage.createEmpty();
    tray = new Tray(icon);
    tray.setToolTip('Ticketscout Local Hub');
    const contextMenu = Menu.buildFromTemplate([
        { label: 'Dashboard öffnen', click: () => {
            const win = new BrowserWindow({ width: 1000, height: 800 });
            win.loadURL(`http://localhost:${config.port}`);
        }},
        { label: 'Beenden', click: () => app.quit() }
    ]);
    tray.setContextMenu(contextMenu);
    
    // Auto-open on start
    const win = new BrowserWindow({ width: 1000, height: 800, title: 'POS Local Hub' });
    win.loadURL(`http://localhost:${config.port}`);
});

app.on('window-all-closed', (e) => {
    e.preventDefault();
});
