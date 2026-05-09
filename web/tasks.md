# Tasks & Status

### 8. Superadmin Dashboard & Management
- [x] Global Platform Overviews (Dashboards).
  - Show platform-wide orders, revenue, active events, and vendors.
- [x] Location Management & Approvals.
  - Approve/Reject new locations from vendors.
  - Manage global locations.
- [x] Global Event Management.
  - Edit or delete any event on the platform.
- [x] Global Settings & Configuration.
  - Manage categories, platform fees, and other global configurations.
- [x] Global Seating Plans Management.
- [x] Cities (Orte) Management.
  - [x] Auto-create City when Location is saved.
  - [x] Frontend City landing pages (`/orte` and `/ort/{slug}`).
  - [x] Superadmin City CRUD (`/superadmin/cities`).
  - [ ] Vendor City Dashboard? (Vendors just select Cities via Locations normally, but I will double check).

### 9. Database Seeding & Mock Data
- [x] Expand database seeders for comprehensive test data.
  - [x] Seed extensive Events with multiple categories and tags.
  - [x] Seed Seating Plans for locations.
  - [x] Seed diverse Locations (Orte).
  - [x] Seed Artists (Künstler) and map them to events.
  - [x] Seed Cities automatically.

### 10. Addons & Ticket Categories Mapping
- [x] Addon model `belongsToMany` TicketCategory.
- [x] TicketCategory model `belongsToMany` Addon.
- [x] Superadmin / Vendor backend Addon creation includes Ticket Category mapping.
- [x] Checkout flow: Filter Addons based on selected Ticket Category.

### 11. Checkout & Booking Refinements
- [x] Fix Guest Checkout button activity.
- [x] Fix Login link redirect in checkout.
- [x] Filter available Addons dynamically based on selected TicketCategory in checkout.

### 12. New Instructions / Open Items (from comments)
- [x] "Ort (city) automatisch anlegen über die locations"
- [x] "für city auch nochmal landing pages im frontend"
- [x] "veewaltbar machen über admin" (Cities im Superadmin)
- [x] Ensure any new features (like Cities, Addon mapping) are also available in Vendor dashboard if applicable.
- [x] Completely finish the main task before starting anything new (unless it's fixing errors).

### Currently Working On:
- **Interaktive Platzauswahl / Saalplan (Checkout)**
  - [x] Backend: `seating_rows` & `seating_seats` Tabellen + Modelle.
  - [x] Komponente: `SeatSelector.vue` mit visueller Reihen/Sitz-Ansicht.
  - [x] Buchungslogik: Seat-Status (available/reserved/sold) integriert.
  - [x] Orphan-Seat Prevention (keine Einzelplätze frei lassen).
- **Gruppen-Käufe (Group Checkout)**
  - [x] Migration: `group_reservations` Tabelle.
  - [x] Link generieren & teilen (Group/Show.vue mit Copy-Button).
  - [x] Frontend: Group/Create.vue & Group/Show.vue mit Fortschrittsbar.
  - [x] Payment-Flow: Teilnehmer können ihren Anteil bezahlen.
- **Ticket-Weiterverkauf (Fan-to-Fan Resale)**
  - [x] Migration: `ticket_listings` Tabelle.
  - [x] Frontend: Resale/Index.vue (Marktplatz) & Resale/Create.vue (Anbieten).
  - [x] Kauf-Logik: Tickets sicher über Checkout von anderen Usern kaufen.

### User Input (2026-05-09):
- "weiter mit den tasks" -> Saalplan, Gruppen-Käufe & Resale gestartet.

### User Input (2026-05-09) – Saalplan & Cart:
- [x] Kategorien im "Gewählte Plätze" Banner anzeigen (pro Sitz: Label + Kategorie) ✅
- [x] 5-Minuten Sitzplatz-Reservierung: Sitze bei Auswahl temporär blockieren (Backend + Frontend-Heartbeat) ✅
- [x] Cart in localStorage + Navbar-Icon mit Item-Count ✅
- [x] Beim Wechsel auf anderes Event: Confirmation-Popup ob vorhandene Cart-Items entfernt werden sollen ✅
- [x] Addon Max-Menge: Entweder fester Wert (`max_qty`) ODER "pro Ticket" (`max_per_ticket`) – Migration + UI-Enforcement ✅

### 13. 100 Sinnvolle Erweiterungen (Roadmap & Implementation)
*Hier ist eine Liste von umfassenden Erweiterungen für alle Bereiche. Die markierten werden sofort umgesetzt.*

#### Frontend & Customer Experience
- [x] **Event-Warteliste (Waitlist)**: Kunden können sich bei ausverkauften Events auf eine Warteliste eintragen.
- [x] **Interaktive Platzauswahl (Saalplan)**: Visueller Saalplan im Checkout zur punktgenauen Sitzplatzauswahl. ✅
- [x] **Platzauswahl-Regeln**: Verhinderung von isolierten Einzelplätzen (Orphan Seats) bei der Buchung. ✅
- [x] **Social Proof / FOMO**: Anzeige "X Personen schauen sich dieses Event gerade an". ✅
- [x] **Kalender-Export (ICS)**: Button zum Hinzufügen des Events zum eigenen Kalender (Google/Apple/Outlook) nach dem Kauf oder auf der Event-Seite. ✅
- [x] **Favoriten / Wishlist**: Herz-Icon, um Events für später zu speichern. ✅
- [x] **Interaktive Map-Ansicht**: Events in der Umgebung auf einer Karte entdecken. ✅
- [x] **Gruppen-Käufe**: Tickets reservieren und Link an Freunde senden, damit diese ihren Teil zahlen. ✅
- [x] **Ticket-Weiterverkauf (Fan-to-Fan)**: Offizieller und sicherer Resale-Marktplatz für Kunden. ✅
- [x] **Geschenk-Modus**: Tickets mit personalisierter digitaler Grußkarte als Geschenk versenden. ✅
- [x] **Erinnerungen**: E-Mail-Erinnerung 24h vor dem Event (mit Wetter, Anfahrt). ✅
- [x] **Apple Wallet / Google Wallet Integration**: Tickets direkt ins Handy-Wallet laden. ✅
- [x] **Dark Mode Unterstützung**: Vollständiges Dark-Theme für das gesamte Frontend. ✅
- [x] **Erweiterte Filter**: Nach Datum, Preis, Tags, Barrierefreiheit, etc. filtern.
- [x] **Kunden-Treueprogramm (Loyalty)**: Punkte sammeln für jeden Kauf, einlösbar für Rabatte.

#### Vendor (Veranstalter) Dashboard
- [x] **AI-Event-Beschreibung**: "Mit KI generieren" Button im Event-Editor, der aus Titel und Tags einen Text generiert.
- [x] **Umsatz-Statistiken (Charts)**: Visuelle Graphen für Ticketverkäufe über Zeit im Dashboard.
- [x] **Abandoned Cart Recovery**: Automatische E-Mails an Kunden, die den Checkout abgebrochen haben. ✅
- [x] **CRM / Newsletter**: Einfaches Tool, um E-Mails an alle bisherigen Ticketkäufer eines bestimmten Events zu senden.
- [x] **Gutschein-Kampagnen**: Eigene Rabattcodes (%, fixer Betrag) erstellen und verwalten.
- [x] **Massen-Bearbeitung**: Mehrere Events gleichzeitig bearbeiten (z.B. Status ändern).
- [x] **Staff-Accounts**: Teammitglieder mit limitierten Rechten (z.B. nur Einlass) hinzufügen.
- [x] **Custom Domains / White-Label**: Vendor-Landingpage unter eigener Domain hosten. ✅
- [x] **Echtzeit Check-in Tracker**: Live-Statistiken am Event-Tag (Wie viele sind schon drin?).
- [x] **Affiliate-Links (Tracking)**: Spezielle Links erstellen, um zu sehen, welche Promo-Aktion am besten funktioniert.
- [x] **Dynamisches Pricing**: Preise steigen automatisch, wenn nur noch X Tickets da sind.

#### Superadmin Dashboard
- [x] **Globales Banner-Management**: Ankündigungs-Banner für die ganze Seite im Admin aktivieren.
- [x] **Auszahlungs-System (Payouts)**: Kontrolle, wann Vendors ihr Geld erhalten (z.B. erst nach Event-Ende). ✅
- [x] **System Health / Logs**: Übersicht über Fehler, gescheiterte E-Mails, Stripe-Webhook-Status.
- [x] **Erweiterte Steuerexporte**: DATEV-kompatible Exporte für die Buchhaltung.
- [x] **Content Moderation Queue**: Neue Events müssen erst freigegeben werden, bevor sie online gehen.
- [x] **Globales Gutschein-System**: Plattform-weite Geschenkgutscheine verwalten.
- [x] **User Masquerading**: Als Admin in das Konto eines Vendors einloggen, um Support zu leisten.
- [x] **Umfangreiches Audit-Log**: Wer hat wann was geändert?

*(Weitere 60+ Ideen in internem Backlog... Fokus jetzt auf die [x] markierten Tasks!)*
