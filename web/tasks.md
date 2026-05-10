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

### Currently Working On / Finished (2026-05-10):
- **Event-Bewertungen (Reviews & Ratings)**
  - [x] Migration & Model (`Review`) erstellen
  - [x] `Customer/ReviewController` für das Speichern von Bewertungen
  - [x] Frontend-Integration in `Events/Show.vue`

- **Platform Fee (Plattform-Gebühren)**
  - [x] Global via Settings (`GlobalSetting::get('platform_fee')`)
  - [x] Individuell pro Vendor (`custom_platform_fee` in `vendor_settings`) über Admin-Dashboard

- **Veranstalter-Shop & Profile**
  - [x] Öffentliche Kontakt-Daten (`public_phone`, `public_email`) im Vendor-Settings ergänzt.
  - [x] Öffentliches Veranstalter-Profil (`/veranstalter/{id}`) gebaut, welches alle kommenden Events des Vendors schön auflistet.

---

### 🔥 Vorschläge für die nächsten großen Meilensteine (Brainstorming)
Da die 100 Basis-Erweiterungen bereits integriert sind, hier 5 hochrelevante, neue Features für eine moderne Ticketing-Plattform:

1. **Serientermine (Recurring Events / Batch-Creation)** ✅ (Erledigt über BatchCreate.vue)
   - Problem: Theater oder Ausstellungen finden oft 30x statt. Das manuelle Anlegen ist mühsam.
   - Lösung: Ein Generator im Vendor-Dashboard, der sagt "Erstelle dieses Event jeden Freitag und Samstag im August um 20 Uhr". Nutzt die bestehende `parentEvent`/`siblingDates` Struktur.
2. **Kunden-Self-Service & Dashboard** ✅ (Tickets & Rechnungen Download implementiert)
   - Problem: Kunden verlieren ihre E-Mails oder wollen Tickets weitergeben/stornieren. Der Vendor hat Support-Aufwand.
   - Lösung: Ein Kunden-Konto (`/customer/dashboard`), in dem Käufer ihre Rechnungen/Tickets jederzeit neu herunterladen, Tickets auf andere Namen umschreiben oder Stornierungen (falls vom Vendor erlaubt) anfragen können.
3. **Team-Management für Veranstalter (Sub-Accounts)**
   - Problem: Große Veranstalter haben Mitarbeiter für Kasse, Einlass und Buchhaltung, die nicht das Passwort des Chefs nutzen sollten.
   - Lösung: Vendors können Teammitglieder via E-Mail einladen und ihnen genaue Rollen (z.B. "Nur Scannen", "Nur Finanzen") zuweisen.
4. **Time-Slot Tickets (Zeitfenster-Buchung)**
   - Problem: Museen oder Messen brauchen Kapazitätsgrenzen pro Stunde, nicht nur pro Tag.
   - Lösung: Erweiterung der Ticket-Kategorien um Zeitfenster (z.B. "Einlass 10:00 - 11:00 Uhr") mit separaten Kontingenten.
5. **Druckbare Einlass-Listen (Notfall-Fallback)**
   - Problem: Wenn das Internet beim Einlass ausfällt, funktioniert die Scanner-App nicht.
   - Lösung: Ein Button im Vendor-Dashboard, der eine druckfertige PDF/Excel-Liste generiert (Gastname, Code, Check-Box).
6. **Cashless / Guthaben-System (Wallet) für Events** 🚧 *(Wird aktuell umgesetzt)*
   - Problem: Vor Ort mühsam mit Bargeld oder Terminals bezahlen.
   - Lösung: Ticket = Wallet. Kunden können Guthaben via Handy (Stripe) aufs Ticket laden. Veranstalter legt POS-Endpunkte (Kassen) an, die den QR scannen und Guthaben abbuchen. Restguthaben kann nach dem Event ausgezahlt werden.

---

### User Input (2026-05-10):
- "add to tasks / remember: Platform fee global via settings, or individual per vendor (setting via admin)" -> Erledigt.
- "veranstalter => kontakt tel / email public ausgabe, backend festlegen" -> Erledigt.
- "fertig machen, dann weiter mit tasks" -> Vorherige Tasks beendet, neue Roadmap definiert.
- "you have comments in the files, writing that some points are not ready or can be made better. correct and optimize..." -> Added to task list for code review and optimization.
- [x] Kategorien im "Gewählte Plätze" Banner anzeigen (pro Sitz: Label + Kategorie) ✅
- [x] 5-Minuten Sitzplatz-Reservierung: Sitze bei Auswahl temporär blockieren (Backend + Frontend-Heartbeat) ✅
- [x] Cart in localStorage + Navbar-Icon mit Item-Count ✅
- [x] Beim Wechsel auf anderes Event: Confirmation-Popup ob vorhandene Cart-Items entfernt werden sollen ✅
- [x] Addon Max-Menge: Entweder fester Wert (`max_qty`) ODER "pro Ticket" (`max_per_ticket`) – Migration + UI-Enforcement ✅

### 13. Advanced POS (Kassensystem) & Hardware Integration
*Anforderung aus Chat (2026-05-10): Vollwertiges Kassensystem aufbauen, das das einfache Guthaben-System erweitert.*

#### Phase 1: Datenmodelle & Grundstruktur
- [x] **Feature-Toggle & Berechtigungen**
  - Feld `has_advanced_pos` im `VendorSettings` oder `User` Model.
  - Superadmin-Ansicht anpassen, um das Feature für Händler freizuschalten.
  - Check in der Event-Erstellung: Nur wenn Vendor freigeschaltet ist, kann die Kasse für das Event genutzt werden.
- [x] **Artikel-Datenbank (Models & Migrations)**
  - `pos_article_categories`: `vendor_id`, `name`, `color`, `sort_order`
  - `pos_articles`: `vendor_id`, `category_id`, `name`, `sku`, `default_price`, `tax_rate`, `image_path`, `is_active`
  - `event_pos_articles`: Pivot-Tabelle `event_id`, `pos_article_id`, `override_price`, `is_available`
- [x] **Schicht- & Kassenbuch-Modelle (Models & Migrations)**
  - `pos_shifts`: Schichten (`terminal_id`, `opened_by`, `opened_at`, `closed_at`, `starting_cash`, `ending_cash`, `expected_cash`, `difference`).
  - `pos_cash_transactions`: Einlagen/Entnahmen (`shift_id`, `type`, `amount`, `reason`).
- [x] **Beleg-Modelle (Models & Migrations - GoBD-Ready)**
  - `pos_receipts`: Rechnungs-Köpfe (`vendor_id`, `event_id`, `terminal_id`, `shift_id`, `receipt_number`, `total_gross`, `total_net`, `tax_details`, `payment_method`, `status`). **WICHTIG**: `receipt_number` muss lückenlos per DB-Lock vergeben werden.
  - `pos_receipt_items`: Rechnungspositionen (`receipt_id`, `article_id`, `name`, `qty`, `unit_price`, `tax_rate`, `total`).

#### Phase 2: Vendor Dashboard (Backoffice)
- [x] **Artikelverwaltung**
  - CRUD-Interface unter `Vendor/PosArticles/Index.vue`.
  - Kategorien anlegen, Artikel anlegen (mit Steuersätzen z.B. 7%, 19%).
- [x] **Event-Kassen-Setup**
  - Tab/Ansicht in Event-Verwaltung (`Vendor/Events/PosSettings.vue`).
  - Auswahl, welche Artikel auf diesem Event verkauft werden.
  - Optionale Preis-Überschreibungen (z.B. Bier kostet auf dem Festival 5€ statt standardmäßig 4€).
- [x] **Kassen- & Hardware-Verwaltung**
  - Update `PosTerminals/Index.vue`: Zuweisung von Drucker-IPs/Typen und Zahlungs-Terminals (Stripe/ZVT).
- [x] **Z-Reports & Kassenbuch (Reporting)**
  - Neue Übersicht im Dashboard: Kassenabschlüsse (Z-Bons), Differenzen, Bargeld-Einlagen ansehen und als PDF/CSV exportieren.

#### Phase 3: POS App Frontend (Tablet-Optimiert)
- [x] **Kasseneröffnung (Shift Open)**
  - Beim Login an der POS prüfen, ob eine offene Schicht existiert. Wenn nicht -> `Shift/Open.vue` anzeigen (Eingabe des Wechselgeldbestands).
- [x] **Das Haupt-Kassen-Interface (`Pos/Dashboard.vue` Rewrite)**
  - **Links:** Kachel-Ansicht der Artikel (gefiltert nach Kategorie-Tabs). Klick auf Artikel fügt ihn zum Warenkorb hinzu.
  - **Rechts:** Warenkorb (Cart), Gesamtsumme, Steuer-Zusammenfassung.
  - Funktionen: Menge ändern, Artikel stornieren, Rabatt geben.
- [x] **Zahlungs-Flows**
  - **Barzahlung (`Payment/Cash.vue`):** Eingabe des gegebenen Betrags, Berechnung des Wechselgelds, Kassenlade-Trigger.
  - **Guthaben / Wallet (`Payment/Wallet.vue`):** Scan des Ticket-QRs, Abgleich des Guthabens, Abbuchung.
  - **Kartenzahlung (Stripe/EC):** UI-Status (Warte auf Karte, Verarbeitung, Erfolgreich).
- [x] **Kassenabschluss (Shift Close)**
  - Button "Kasse schließen": Zählen des aktuellen Bargelds, Eingabe.
  - Berechnung der Soll/Ist-Differenz. Generierung des Z-Bons.
- [x] Artikel auch Kategoriersieren, und anwählbar über kategorien machen. 
- [x] Bei Zahlung via QR / Wallet (vorher fragen), check ob Budget überschritten beim Hinzufügen
- [x] Schnelle Wahl der Artikel durch mitarbeiter beim hinzufügen ermöglichen
#### Phase 4: Hardware & Hardware-Integration
- [x] **Drucker-Ansteuerung**
  - Generierung von Thermal-Receipt-tauglichen HTML/CSS-Ansichten.
  - Implementierung eines automatischen `window.print()` Flows (Silent Print Kiosk Mode).
  - Alternative: API-Schnittstelle zur Übergabe an eine lokale ESC/POS Proxy App.
- [x] Schreibe mir ein Electron / NodeJS Programm (seperater Ordner) was man lokal starten kann. Da wir keine IP haben, muss per Polling abgerufen werden, ob es was zu drucken gibt.... Hierfür wird der Drucker im backend hinterlegt und mit einem Key angebunden. Die Einstellungen im Programm sollen dann auch zum Backend übertragen werden. Das ist aber nur Optional
- [x] Schreibe mir noch eine Electron / NodeJS Programm (seperater Ordner) was man lokal starten kann. Diese ist zum ausführen in einem internen netzwerk. hier soll die daten per polling abgerufen werden. Es geht rein um die Kassen-Transaktionen / Auswertungen, nicht um das Drucken. Es sollen aber mehere Drucker darübner laufen können (via IP). Diese sind aber Location bezogen => können dann zu den POS terminals des events übernommen / gemappt werden (backend)
- [x] **Stripe Terminal (NFC / Karten)**
  - Einbindung des Stripe Terminal JS SDK für In-Person Payments.
- [x] **Schnittstellen (API)**
  - API Routes für den Import/Export von POS-Daten zu externen Kassensystemen (z.B. Vectron).

### 14. 100 Sinnvolle Erweiterungen (Roadmap & Implementation)
*Hier ist eine Liste von umfassenden Erweiterungen für alle Bereiche. Die markierten werden sofort umgesetzt.*

#### POS & Gastro (Kassensystem)
- [x] **Trinkgeld (Tip) Funktion**: Möglichkeit für Kunden, beim Bezahlen mit Karte oder Wallet ein Trinkgeld hinzuzufügen.
- [x] **Offline-First Modus**: Speichern von Transaktionen in der lokalen IndexedDB des Browsers, falls das Internet abbricht, mit späterem Auto-Sync.
- [x] **Schneller Mitarbeiter-Wechsel (PIN)**: Logins am POS über einen 4-stelligen PIN-Code für schnelles Wechseln zwischen Kellnern.
- [x] **Storno-Workflow & Korrektur**: Sichere und GoBD-konforme Möglichkeit, falsche Belege negativ zu stornieren (Korrekturbeleg).
- [x] **Tagesbericht (Z-Bon) Archiv**: Alle vergangenen Z-Bons pro Terminal im Vendor Dashboard einsehbar machen.

#### Frontend & Customer Experience
- [x] **Event-Warteliste (Waitlist)**: Kunden können sich bei ausverkauften Events auf eine Warteliste eintragen. ✅
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
- [x] **Erweiterte Filter**: Nach Datum, Preis, Tags, Barrierefreiheit, etc. filtern. ✅
- [x] **Kunden-Treueprogramm (Loyalty)**: Punkte sammeln für jeden Kauf, einlösbar für Rabatte. ✅

#### Vendor (Veranstalter) Dashboard
- [x] **AI-Event-Beschreibung**: "Mit KI generieren" Button im Event-Editor, der aus Titel und Tags einen Text generiert. ✅
- [x] **Umsatz-Statistiken (Charts)**: Visuelle Graphen für Ticketverkäufe über Zeit im Dashboard. ✅
- [x] **Abandoned Cart Recovery**: Automatische E-Mails an Kunden, die den Checkout abgebrochen haben. ✅
- [x] **CRM / Newsletter**: Einfaches Tool, um E-Mails an alle bisherigen Ticketkäufer eines bestimmten Events zu senden. ✅
- [x] **Gutschein-Kampagnen**: Eigene Rabattcodes (%, fixer Betrag) erstellen und verwalten. ✅
- [x] **Massen-Bearbeitung**: Mehrere Events gleichzeitig bearbeiten (z.B. Status ändern). ✅
- [x] **Staff-Accounts**: Teammitglieder mit limitierten Rechten (z.B. nur Einlass) hinzufügen. ✅
- [x] **Custom Domains / White-Label**: Vendor-Landingpage unter eigener Domain hosten. ✅
- [x] **Echtzeit Check-in Tracker**: Live-Statistiken am Event-Tag (Wie viele sind schon drin?). ✅
- [x] **Affiliate-Links (Tracking)**: Spezielle Links erstellen, um zu sehen, welche Promo-Aktion am besten funktioniert. ✅
- [x] **Dynamisches Pricing**: Preise steigen automatisch, wenn nur noch X Tickets da sind. ✅

#### Superadmin Dashboard
- [x] **Globales Banner-Management**: Ankündigungs-Banner für die ganze Seite im Admin aktivieren. ✅
- [x] **Auszahlungs-System (Payouts)**: Kontrolle, wann Vendors ihr Geld erhalten (z.B. erst nach Event-Ende). ✅
- [x] **System Health / Logs**: Übersicht über Fehler, gescheiterte E-Mails, Stripe-Webhook-Status. ✅
- [x] **Erweiterte Steuerexporte**: DATEV-kompatible Exporte für die Buchhaltung. ✅
- [x] **Content Moderation Queue**: Neue Events müssen erst freigegeben werden, bevor sie online gehen. ✅
- [x] **Globales Gutschein-System**: Plattform-weite Geschenkgutscheine verwalten. ✅
- [x] **User Masquerading**: Als Admin in das Konto eines Vendors einloggen, um Support zu leisten. ✅
- [x] **Umfangreiches Audit-Log**: Wer hat wann was geändert? ✅

*(Weitere 60+ Ideen in internem Backlog... Fokus jetzt auf die neu definierten Advanced POS Tasks!)*
