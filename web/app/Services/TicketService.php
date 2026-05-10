<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketTemplate;
use App\Models\Order;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Common\EccLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    /**
     * Generate a unique QR code hash for a ticket.
     */
    public function generateQrHash(Ticket $ticket): string
    {
        return hash('sha256', $ticket->id . '-' . $ticket->order_id . '-' . uniqid());
    }

    /**
     * Generate the QR Code image as a base64 string.
     */
    public function getQrCodeBase64(string $hash): string
    {
        $options = new QROptions([
            'version'      => 5,
            'outputType'   => QROutputInterface::MARKUP_SVG ?? 'svg',
            'eccLevel'     => EccLevel::L,
        ]);

        $qrcode = new QRCode($options);
        // We use the validation endpoint url as the content of the QR code
        $url = route('scanner.validate', ['hash' => $hash]);
        return $qrcode->render($url);
    }

    /**
     * Render the Ticket PDF.
     */
    public function generatePdf(Ticket $ticket, TicketTemplate $template)
    {
        $qrCodeSvg = $this->getQrCodeBase64($ticket->qr_code_hash);
        $customerName = $ticket->order->user ? $ticket->order->user->name : 'Guest';
        $eventName = $ticket->ticketCategory->event->title;
        $categoryName = $ticket->ticketCategory->name;

        // Replace placeholders in HTML content
        $html = $template->html_content;
        $html = str_replace('{{ customer_name }}', $customerName, $html);
        $html = str_replace('{{ event_name }}', $eventName, $html);
        $html = str_replace('{{ category_name }}', $categoryName, $html);
        
        $eventDate = $ticket->ticketCategory->event->start_date ? \Carbon\Carbon::parse($ticket->ticketCategory->event->start_date)->format('d.m.Y H:i') : '';
        $vendorName = $ticket->ticketCategory->event->vendor ? $ticket->ticketCategory->event->vendor->name : '';
        $seatInfo = $ticket->seat_info ? 'Platz ' . $ticket->seat_info : 'Freie Platzwahl';
        
        $html = str_replace('{{ event_date }}', $eventDate, $html);
        $html = str_replace('{{ vendor_name }}', $vendorName, $html);
        $html = str_replace('{{ seat_info }}', $seatInfo, $html);
        $html = str_replace('{{ qr_code }}', '<img src="'. $qrCodeSvg . '" alt="QR Code" style="width: 100%; height: 100%;" />', $html);

        // Gift Mode Injection
        $giftHtml = '';
        if ($ticket->order && $ticket->order->is_gift) {
            $giftHtml = '<div style="margin-top: 20px; padding: 15px; border: 2px dashed #ec4899; border-radius: 10px; background-color: #fdf2f8; text-align: center;">';
            $giftHtml .= '<h3 style="color: #db2777; margin-top: 0;">Ein Geschenk für ' . e($ticket->order->gift_recipient_name ?: 'Dich') . '</h3>';
            if ($ticket->order->gift_message) {
                $giftHtml .= '<p style="font-style: italic; color: #be185d;">"' . nl2br(e($ticket->order->gift_message)) . '"</p>';
            }
            $giftHtml .= '</div>';
            
            // If the template has a {{ gift_message }} placeholder, use it, otherwise append to body
            if (strpos($html, '{{ gift_message }}') !== false) {
                $html = str_replace('{{ gift_message }}', $giftHtml, $html);
            } else {
                $html .= $giftHtml;
            }
        } else {
            $html = str_replace('{{ gift_message }}', '', $html);
        }

        // Apply CSS
        $fullHtml = '<html><head><style>' . $template->css_content . '</style></head><body>' . $html . '</body></html>';

        return Pdf::loadHTML($fullHtml)->setPaper('a4', 'portrait')->output();
    }

    /**
     * Generate a single PDF containing all tickets for an order.
     */
    public function generatePdfForOrder(Order $order)
    {
        $template = null;
        if ($order->event && $order->event->ticket_template_id) {
            $template = \App\Models\TicketTemplate::find($order->event->ticket_template_id);
        }
        if (!$template && $order->event) {
            $template = $order->event->vendor->ticketTemplates()->first();
        }
        if (!$template) {
            $template = new TicketTemplate([
                'html_content' => '<h1>Ticket</h1><p>{{ event_name }}</p><p>{{ customer_name }}</p><p>{{ category_name }}</p><div>{{ qr_code }}</div>',
                'css_content' => 'body { font-family: sans-serif; text-align: center; } h1 { color: #14b8a6; } .page-break { page-break-after: always; }'
            ]);
        }

        $combinedHtml = '<html><head><style>' . $template->css_content . ' .page-break { page-break-after: always; }</style></head><body>';
        
        $tickets = $order->tickets;
        $count = $tickets->count();
        
        foreach ($tickets as $index => $ticket) {
            $qrCodeSvg = $this->getQrCodeBase64($ticket->qr_code_hash);
            $customerName = $ticket->order->user ? $ticket->order->user->name : ($ticket->order->guest_name ?: 'Guest');
            $eventName = $ticket->ticketCategory->event->title;
            $categoryName = $ticket->ticketCategory->name;

            $html = $template->html_content;
            $html = str_replace('{{ customer_name }}', $customerName, $html);
            $html = str_replace('{{ event_name }}', $eventName, $html);
            $html = str_replace('{{ category_name }}', $categoryName, $html);
            
            $eventDate = $ticket->ticketCategory->event->start_date ? \Carbon\Carbon::parse($ticket->ticketCategory->event->start_date)->format('d.m.Y H:i') : '';
            $vendorName = $ticket->ticketCategory->event->vendor ? $ticket->ticketCategory->event->vendor->name : '';
            $seatInfo = $ticket->seat_info ? 'Platz ' . $ticket->seat_info : 'Freie Platzwahl';
            
            $html = str_replace('{{ event_date }}', $eventDate, $html);
            $html = str_replace('{{ vendor_name }}', $vendorName, $html);
            $html = str_replace('{{ seat_info }}', $seatInfo, $html);
            $html = str_replace('{{ qr_code }}', '<img src="'. $qrCodeSvg . '" alt="QR Code" style="width: 100%; height: 100%;" />', $html);

            // Gift Mode Injection
            $giftHtml = '';
            if ($ticket->order && $ticket->order->is_gift) {
                $giftHtml = '<div style="margin-top: 20px; padding: 15px; border: 2px dashed #ec4899; border-radius: 10px; background-color: #fdf2f8; text-align: center;">';
                $giftHtml .= '<h3 style="color: #db2777; margin-top: 0;">Ein Geschenk für ' . e($ticket->order->gift_recipient_name ?: 'Dich') . '</h3>';
                if ($ticket->order->gift_message) {
                    $giftHtml .= '<p style="font-style: italic; color: #be185d;">"' . nl2br(e($ticket->order->gift_message)) . '"</p>';
                }
                $giftHtml .= '</div>';
                
                if (strpos($html, '{{ gift_message }}') !== false) {
                    $html = str_replace('{{ gift_message }}', $giftHtml, $html);
                } else {
                    $html .= $giftHtml;
                }
            } else {
                $html = str_replace('{{ gift_message }}', '', $html);
            }
            
            $combinedHtml .= '<div class="ticket-container">' . $html . '</div>';
            
            if ($index < $count - 1) {
                $combinedHtml .= '<div class="page-break"></div>';
            }
        }
        
        $combinedHtml .= '</body></html>';

        return Pdf::loadHTML($combinedHtml)->setPaper('a4', 'portrait')->output();
    }

    /**
     * Generate tickets for a complete order.
     */
    public function generateTicketsForOrder(Order $order)
    {
        // Use event's assigned template, or fallback to first template, or a new instance if none exists
        $template = null;
        if ($order->event && $order->event->ticket_template_id) {
            $template = \App\Models\TicketTemplate::find($order->event->ticket_template_id);
        }
        
        if (!$template) {
            $template = $order->event->vendor->ticketTemplates()->first() ?? new TicketTemplate([
                'html_content' => '<h1>Ticket</h1><p>{{ event_name }}</p><p>{{ customer_name }}</p><p>{{ category_name }}</p><div>{{ qr_code }}</div>',
                'css_content' => 'body { font-family: sans-serif; text-align: center; } h1 { color: #14b8a6; }'
            ]);
        }

        foreach ($order->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $qrHash = hash('sha256', $order->id . '-' . $item->id . '-' . $i . '-' . uniqid());
                
                $ticket = Ticket::create([
                    'order_id' => $order->id,
                    'ticket_category_id' => $item->ticket_category_id,
                    'qr_code_hash' => $qrHash,
                    'status' => 'valid',
                ]);

                // Generate PDF
                $this->generatePdf($ticket, $template);

                // Generate Wallet Pass (Placeholder logic)
                $this->generateAppleWalletPass($ticket);
            }
        }
    }

    /**
     * Generate an Apple Wallet .pkpass file for the ticket.
     * Note: This requires real certificates to produce a valid pass.
     * This method builds the JSON structure and creates a mock .pkpass file.
     */
    public function generateAppleWalletPass(Ticket $ticket)
    {
        $order = $ticket->order;
        $event = $order->event;
        
        $passJson = [
            "formatVersion" => 1,
            "passTypeIdentifier" => "pass.com.ticketplatform.event",
            "serialNumber" => $ticket->qr_code_hash,
            "teamIdentifier" => "YOUR_TEAM_ID",
            "organizationName" => $event->vendor->name,
            "description" => $event->title . " Ticket",
            "logoText" => $event->title,
            "foregroundColor" => "rgb(255, 255, 255)",
            "backgroundColor" => "rgb(20, 184, 166)", // brand-500
            "barcode" => [
                "message" => $ticket->qr_code_hash,
                "format" => "PKBarcodeFormatQR",
                "messageEncoding" => "iso-8859-1"
            ],
            "eventTicket" => [
                "primaryFields" => [
                    [
                        "key" => "event",
                        "label" => "EVENT",
                        "value" => $event->title
                    ]
                ],
                "secondaryFields" => [
                    [
                        "key" => "loc",
                        "label" => "LOCATION",
                        "value" => $event->location ? $event->location->city : "TBA"
                    ]
                ]
            ]
        ];

        // In a real scenario, we would use a library like PKPass to sign this JSON with certs
        // and zip it with icon.png, logo.png into a .pkpass file.
        // For now, we save the JSON structure as a placeholder.
        $path = "tickets/passes/{$ticket->id}.json";
        Storage::disk('local')->put($path, json_encode($passJson, JSON_PRETTY_PRINT));
        
        return $path;
    }
}
