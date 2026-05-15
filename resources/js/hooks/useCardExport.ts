import html2canvas from 'html2canvas';
import { useRef, useState } from 'react';

// Physical card: ISO/IEC 7810 ID-1 = 85.6 × 54 mm
// Rendered at 342 × 216 px (4 px/mm).  Exported at 3× = 1026 × 648 px.
export const CARD_W = 342;
export const CARD_H = Math.round(CARD_W * 54 / 85.6); // 216

async function captureElement(el: HTMLElement): Promise<string> {
    const canvas = await html2canvas(el, {
        scale: 3,
        useCORS: true,
        allowTaint: true,
        backgroundColor: null,
        width: CARD_W,
        height: CARD_H,
        logging: false,
    });
    return canvas.toDataURL('image/png');
}

function printDataUrl(dataUrl: string, cardNumber: string, side: string): void {
    const win = window.open('', '_blank', `width=${CARD_W},height=${CARD_H}`);
    if (!win) { alert('Allow popups to print the card.'); return; }
    win.document.write(`<!DOCTYPE html>
<html><head><title>ID Card ${cardNumber} ${side}</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{display:flex;align-items:center;justify-content:center;background:#fff}
  img{width:85.6mm;height:54mm;display:block}
  @page{margin:0;size:85.6mm 54mm}
</style></head>
<body><img src="${dataUrl}"/><script>
  window.onload=function(){setTimeout(function(){window.print();window.close();},300)};
<\/script></body></html>`);
    win.document.close();
}

export function useCardExport(cardId: string, cardNumber: string) {
    const [exporting, setExporting] = useState(false);
    const frontRef = useRef<HTMLDivElement>(null);
    const backRef  = useRef<HTMLDivElement>(null);

    async function auditExport(
        side: 'front' | 'back' | 'both',
        action: 'print' | 'export_png' = 'export_png',
    ): Promise<void> {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
        const response = await fetch(route('id-cards.export.audit', cardId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta?.content ?? '',
            },
            body: JSON.stringify({ side, action }),
        });
        if (!response.ok) throw new Error('Not authorized');
    }

    async function exportFront(): Promise<void> {
        if (!frontRef.current) return;
        setExporting(true);
        try {
            await auditExport('front', 'export_png');
            const dataUrl = await captureElement(frontRef.current);
            const link = document.createElement('a');
            link.download = `id-card-${cardNumber}-front.png`;
            link.href = dataUrl;
            link.click();
        } catch (e) {
            console.error('Export failed', e);
        } finally {
            setExporting(false);
        }
    }

    async function exportBack(): Promise<void> {
        if (!backRef.current) return;
        setExporting(true);
        try {
            await auditExport('back', 'export_png');
            const dataUrl = await captureElement(backRef.current);
            const link = document.createElement('a');
            link.download = `id-card-${cardNumber}-back.png`;
            link.href = dataUrl;
            link.click();
        } catch (e) {
            console.error('Export failed', e);
        } finally {
            setExporting(false);
        }
    }

    async function printCard(side: 'front' | 'back'): Promise<void> {
        const ref = side === 'front' ? frontRef : backRef;
        if (!ref.current) return;
        setExporting(true);
        try {
            await auditExport(side, 'print');
            const dataUrl = await captureElement(ref.current);
            printDataUrl(dataUrl, cardNumber, side);
        } catch (e) {
            console.error('Print failed', e);
        } finally {
            setExporting(false);
        }
    }

    return { frontRef, backRef, exporting, exportFront, exportBack, printCard };
}
