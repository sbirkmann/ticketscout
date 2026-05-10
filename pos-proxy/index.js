const { app, BrowserWindow, ipcMain, Tray, Menu } = require('electron');
const path = require('path');
const axios = require('axios');
const fs = require('fs');

let tray = null;
let printWindow = null;
let pollingInterval = null;

const CONFIG_FILE = path.join(app.getPath('userData'), 'pos-config.json');

let config = {
    apiUrl: 'http://localhost:8000/api/pos',
    terminalId: '',
    apiKey: '',
    printerName: '',
    pollingRateMs: 5000
};

// Load config
if (fs.existsSync(CONFIG_FILE)) {
    try {
        const data = fs.readFileSync(CONFIG_FILE, 'utf-8');
        config = { ...config, ...JSON.parse(data) };
    } catch (e) {
        console.error('Error loading config', e);
    }
}

function saveConfig() {
    fs.writeFileSync(CONFIG_FILE, JSON.stringify(config, null, 2));
}

function createConfigWindow() {
    const win = new BrowserWindow({
        width: 500,
        height: 600,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        },
        title: 'POS Printer Proxy Configuration'
    });

    const printers = win.webContents.getPrintersAsync().then(printers => {
        const printerListHtml = printers.map(p => `<option value="${p.name}" ${p.name === config.printerName ? 'selected' : ''}>${p.name}</option>`).join('');
        
        const html = `
            <html>
            <body style="font-family: sans-serif; padding: 20px; background: #f4f4f5;">
                <h2 style="margin-top:0">Ticketscout POS Proxy</h2>
                <div style="margin-bottom: 10px;">
                    <label>API URL</label><br>
                    <input type="text" id="apiUrl" value="${config.apiUrl}" style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <label>Terminal ID</label><br>
                    <input type="text" id="terminalId" value="${config.terminalId}" style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <label>API Key (App Key)</label><br>
                    <input type="text" id="apiKey" value="${config.apiKey}" style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <label>Drucker (System Printer)</label><br>
                    <select id="printerName" style="width: 100%; padding: 8px;">
                        <option value="">-- Standard Drucker nutzen --</option>
                        ${printerListHtml}
                    </select>
                </div>
                <button onclick="save()" style="padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 5px; cursor: pointer;">Speichern & Starten</button>
                <div id="status" style="margin-top: 15px; color: green; font-weight: bold;"></div>
                
                <script>
                    const { ipcRenderer } = require('electron');
                    function save() {
                        const apiUrl = document.getElementById('apiUrl').value;
                        const terminalId = document.getElementById('terminalId').value;
                        const apiKey = document.getElementById('apiKey').value;
                        const printerName = document.getElementById('printerName').value;
                        ipcRenderer.send('save-config', { apiUrl, terminalId, apiKey, printerName });
                        document.getElementById('status').innerText = 'Gespeichert! Polling gestartet...';
                    }
                </script>
            </body>
            </html>
        `;
        
        win.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(html)}`);
    });
}

function startPolling() {
    if (pollingInterval) clearInterval(pollingInterval);
    
    if (!config.terminalId || !config.apiKey) {
        console.log('Missing credentials, polling not started.');
        return;
    }

    console.log('Started polling for terminal:', config.terminalId);
    
    pollingInterval = setInterval(async () => {
        try {
            const res = await axios.get(`${config.apiUrl}/print-jobs`, {
                params: { terminal_id: config.terminalId },
                headers: { 'X-API-KEY': config.apiKey },
                timeout: 3000
            });

            const jobs = res.data.jobs;
            if (jobs && jobs.length > 0) {
                console.log(`Found ${jobs.length} jobs to print.`);
                for (const job of jobs) {
                    await processJob(job);
                }
            }
        } catch (error) {
            console.error('Polling error:', error.message);
        }
    }, config.pollingRateMs);
}

function processJob(job) {
    return new Promise((resolve) => {
        if (!printWindow) {
            printWindow = new BrowserWindow({ show: false, webPreferences: { nodeIntegration: false } });
        }

        // We wrap the HTML in a proper container styled for 80mm thermal printers
        const printHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { margin: 0; padding: 0; font-family: monospace; font-size: 12px; color: #000; width: 80mm; }
                    .print-container { width: 100%; padding: 10px; box-sizing: border-box; }
                </style>
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="print-container">
                    ${job.html_content}
                </div>
            </body>
            </html>
        `;

        printWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(printHtml)}`);
        
        printWindow.webContents.once('did-finish-load', () => {
            const printOptions = {
                silent: true,
                printBackground: true,
                margins: { marginType: 'none' }
            };

            if (config.printerName) {
                printOptions.deviceName = config.printerName;
            }

            printWindow.webContents.print(printOptions, async (success, failureReason) => {
                console.log(`Print job ${job.id} finished: success=${success}`);
                
                // Report status back to backend
                try {
                    await axios.post(`${config.apiUrl}/print-jobs/${job.id}/status`, {
                        status: success ? 'printed' : 'failed'
                    }, {
                        params: { terminal_id: config.terminalId },
                        headers: { 'X-API-KEY': config.apiKey }
                    });
                } catch (e) {
                    console.error('Failed to update job status:', e.message);
                }

                resolve();
            });
        });
    });
}

app.whenReady().then(() => {
    // Hidden tray app
    const { nativeImage } = require('electron');
    const icon = nativeImage.createEmpty(); // simple empty icon so it doesn't crash
    tray = new Tray(icon);
    const contextMenu = Menu.buildFromTemplate([
        { label: 'Konfiguration', type: 'normal', click: () => createConfigWindow() },
        { label: 'Beenden', type: 'normal', click: () => app.quit() }
    ]);
    tray.setToolTip('POS Print Proxy');
    tray.setContextMenu(contextMenu);

    if (!config.terminalId || !config.apiKey) {
        createConfigWindow();
    } else {
        startPolling();
    }
});

app.on('window-all-closed', (e) => {
    e.preventDefault(); // keep running in background
});

ipcMain.on('save-config', (event, newConfig) => {
    config = { ...config, ...newConfig };
    saveConfig();
    startPolling();
});
