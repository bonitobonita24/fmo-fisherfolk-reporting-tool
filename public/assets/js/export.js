/**
 * Fisherfolk List export — PDF (A4) and Excel.
 * Exports exactly the currently filtered/sorted dataset (filteredFisherfolkData
 * from charts.js), with a summary (totals, per-barangay, gender, age) first.
 */
(function () {
    'use strict';

    const ORG_TITLE = 'Fisheries Management Office';
    const ORG_SUBTITLE = 'City Government of Calapan';
    const BRAND = [234, 88, 12]; // orange-600

    const CATEGORY_LABELS = {
        boat_owneroperator: 'Boat Owner/Operator',
        capture_fishing: 'Capture Fishing',
        gleaning: 'Gleaning',
        vendor: 'Vendor',
        fish_processing: 'Fish Processing',
        aquaculture: 'Aquaculture'
    };
    const CATEGORY_KEYS = Object.keys(CATEGORY_LABELS);

    const AGE_BUCKETS = ['Under 25', '25-34', '35-44', '45-54', '55-64', '65 and above', 'Unknown'];

    // ---- helpers ---------------------------------------------------------

    function getData() {
        // filteredFisherfolkData is a script-scoped binding from charts.js
        try {
            return Array.isArray(filteredFisherfolkData) ? filteredFisherfolkData : [];
        } catch (e) {
            return [];
        }
    }

    function selText(id) {
        const el = document.getElementById(id);
        if (!el || el.selectedIndex < 0) return '';
        return el.options[el.selectedIndex].text.trim();
    }

    function getContext() {
        const barangayVal = (document.getElementById('barangayFilterList') || {}).value || 'all';
        const categoryVal = (document.getElementById('categoryFilter') || {}).value || 'all';
        const searchTerm = ((document.getElementById('searchInput') || {}).value || '').trim();

        const barangayLabel = barangayVal === 'all' ? 'All Barangays' : (selText('barangayFilterList') || barangayVal);
        const categoryLabel = categoryVal === 'all' ? null : (CATEGORY_LABELS[categoryVal] || selText('categoryFilter'));

        const parts = [barangayLabel];
        if (categoryLabel) parts.push(categoryLabel);
        if (searchTerm) parts.push('Search: "' + searchTerm + '"');

        const subtitle = 'Registered Fisherfolk — ' + parts.join(' — ');

        const safe = s => String(s).replace(/[^A-Za-z0-9]+/g, '');
        const d = new Date();
        const stamp = d.getFullYear() + pad(d.getMonth() + 1) + pad(d.getDate());
        let fileBase = 'Fisherfolk_' + safe(barangayLabel);
        if (categoryLabel) fileBase += '_' + safe(categoryLabel);
        fileBase += '_' + stamp;

        return { subtitle, fileBase, generated: d };
    }

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function fmtDateTime(d) {
        return d.toLocaleString('en-PH', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function calcAge(dob) {
        if (!dob) return null;
        const m = String(dob).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return null;
        const by = +m[1], bm = +m[2], bd = +m[3];
        const now = new Date();
        let age = now.getFullYear() - by;
        const mo = (now.getMonth() + 1) - bm;
        if (mo < 0 || (mo === 0 && now.getDate() < bd)) age--;
        return (age >= 0 && age < 130) ? age : null;
    }

    function ageBucket(age) {
        if (age === null) return 'Unknown';
        if (age < 25) return 'Under 25';
        if (age <= 34) return '25-34';
        if (age <= 44) return '35-44';
        if (age <= 54) return '45-54';
        if (age <= 64) return '55-64';
        return '65 and above';
    }

    function activeCategories(item) {
        return CATEGORY_KEYS.filter(k => String(item[k]) === '1').map(k => CATEGORY_LABELS[k]);
    }

    function buildSummary(data) {
        const gender = { Male: 0, Female: 0, Other: 0 };
        const age = {};
        AGE_BUCKETS.forEach(b => age[b] = 0);
        const barangay = {};

        data.forEach(item => {
            const s = (item.sex || '').toLowerCase();
            if (s.startsWith('m')) gender.Male++;
            else if (s.startsWith('f')) gender.Female++;
            else gender.Other++;

            age[ageBucket(calcAge(item.date_of_birth))]++;

            const b = item.address || '(Unknown)';
            barangay[b] = (barangay[b] || 0) + 1;
        });

        const byBarangay = Object.keys(barangay)
            .map(b => [b, barangay[b]])
            .sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0]));

        return { total: data.length, gender, age, byBarangay };
    }

    function withBusy(btn, fn) {
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        // let the DOM paint the busy state before the (sync) heavy work
        setTimeout(function () {
            try { fn(); }
            catch (e) { console.error(e); alert('Export failed: ' + e.message); }
            finally { btn.disabled = false; btn.innerHTML = original; }
        }, 30);
    }

    // ---- PDF -------------------------------------------------------------

    function exportPDF() {
        const data = getData();
        if (!data.length) { alert('No records to export for the current filters.'); return; }

        const ctx = getContext();
        const summary = buildSummary(data);
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'pt', format: 'a4', orientation: 'portrait' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 40;
        let y = 46;

        // Header
        doc.setFont('helvetica', 'bold').setFontSize(16).setTextColor(20);
        doc.text(ORG_TITLE, pageW / 2, y, { align: 'center' }); y += 16;
        doc.setFont('helvetica', 'normal').setFontSize(10).setTextColor(90);
        doc.text(ORG_SUBTITLE, pageW / 2, y, { align: 'center' }); y += 18;
        doc.setFont('helvetica', 'bold').setFontSize(12).setTextColor(BRAND[0], BRAND[1], BRAND[2]);
        doc.text(ctx.subtitle, pageW / 2, y, { align: 'center', maxWidth: pageW - 2 * margin }); y += 16;
        doc.setFont('helvetica', 'normal').setFontSize(8).setTextColor(120);
        doc.text('Generated: ' + fmtDateTime(ctx.generated) + '     Total records: ' + summary.total,
            pageW / 2, y, { align: 'center' });
        doc.setTextColor(20);
        y += 14;

        // Summary heading
        doc.setFont('helvetica', 'bold').setFontSize(11);
        doc.text('Summary', margin, y); y += 6;

        const halfW = (pageW - 2 * margin - 14) / 2;
        const summaryTableOpts = {
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 3 },
            headStyles: { fillColor: BRAND, textColor: 255, fontStyle: 'bold' },
            margin: { left: margin }
        };

        // Gender (left)
        doc.autoTable(Object.assign({}, summaryTableOpts, {
            startY: y,
            tableWidth: halfW,
            head: [['Gender', 'Count']],
            body: [
                ['Male', summary.gender.Male],
                ['Female', summary.gender.Female]
            ].concat(summary.gender.Other ? [['Unspecified', summary.gender.Other]] : [])
                .concat([[{ content: 'Total', styles: { fontStyle: 'bold' } },
                          { content: summary.total, styles: { fontStyle: 'bold' } }]])
        }));
        const genderEndY = doc.lastAutoTable.finalY;

        // Age distribution (right)
        doc.autoTable(Object.assign({}, summaryTableOpts, {
            startY: y,
            tableWidth: halfW,
            margin: { left: margin + halfW + 14 },
            head: [['Age Group', 'Count']],
            body: AGE_BUCKETS.filter(b => summary.age[b] > 0 || b !== 'Unknown')
                .map(b => [b, summary.age[b]])
        }));
        const ageEndY = doc.lastAutoTable.finalY;

        // Per-barangay (full width)
        doc.autoTable(Object.assign({}, summaryTableOpts, {
            startY: Math.max(genderEndY, ageEndY) + 12,
            tableWidth: pageW - 2 * margin,
            head: [['Barangay', 'Fisherfolk Count']],
            body: summary.byBarangay,
            columnStyles: { 1: { halign: 'right', cellWidth: 110 } }
        }));

        // Records table
        let recY = doc.lastAutoTable.finalY + 18;
        doc.setFont('helvetica', 'bold').setFontSize(11);
        doc.text('Fisherfolk Records (' + summary.total + ')', margin, recY);
        recY += 6;

        const body = data.map((it, i) => ([
            i + 1,
            it.id_number || '',
            it.full_name || '',
            it.rsbsa || '',
            it.address || '',
            it.sex || '',
            it.contact_number || '',
            activeCategories(it).join(', ')
        ]));

        doc.autoTable({
            startY: recY,
            theme: 'striped',
            styles: { fontSize: 7, cellPadding: 3, overflow: 'linebreak', valign: 'top' },
            headStyles: { fillColor: BRAND, textColor: 255, fontStyle: 'bold' },
            margin: { left: margin, right: margin, bottom: 30 },
            head: [['#', 'ID Number', 'Full Name', 'RSBSA', 'Barangay', 'Sex', 'Contact', 'Activity Categories']],
            body: body,
            columnStyles: {
                0: { cellWidth: 24, halign: 'right' },
                1: { cellWidth: 78 },
                2: { cellWidth: 104 },
                3: { cellWidth: 80 },
                4: { cellWidth: 66 },
                5: { cellWidth: 30 },
                6: { cellWidth: 54 },
                7: { cellWidth: 79 }
            }
        });

        // Footer: page numbers
        const pages = doc.getNumberOfPages();
        const pageH = doc.internal.pageSize.getHeight();
        for (let p = 1; p <= pages; p++) {
            doc.setPage(p);
            doc.setFont('helvetica', 'normal').setFontSize(7).setTextColor(130);
            doc.text(ORG_TITLE, margin, pageH - 16);
            doc.text('Page ' + p + ' of ' + pages, pageW - margin, pageH - 16, { align: 'right' });
        }

        doc.save(ctx.fileBase + '.pdf');
    }

    // ---- Excel -----------------------------------------------------------

    function exportExcel() {
        const data = getData();
        if (!data.length) { alert('No records to export for the current filters.'); return; }

        const ctx = getContext();
        const summary = buildSummary(data);
        const wb = XLSX.utils.book_new();

        // Summary sheet
        const aoa = [];
        aoa.push([ORG_TITLE]);
        aoa.push([ORG_SUBTITLE]);
        aoa.push([ctx.subtitle]);
        aoa.push(['Generated', fmtDateTime(ctx.generated)]);
        aoa.push([]);
        aoa.push(['Total Fisherfolk', summary.total]);
        aoa.push([]);
        aoa.push(['Gender', 'Count']);
        aoa.push(['Male', summary.gender.Male]);
        aoa.push(['Female', summary.gender.Female]);
        if (summary.gender.Other) aoa.push(['Unspecified', summary.gender.Other]);
        aoa.push([]);
        aoa.push(['Age Group', 'Count']);
        AGE_BUCKETS.forEach(b => { if (summary.age[b] > 0 || b !== 'Unknown') aoa.push([b, summary.age[b]]); });
        aoa.push([]);
        aoa.push(['Barangay', 'Fisherfolk Count']);
        summary.byBarangay.forEach(r => aoa.push(r));

        const wsSummary = XLSX.utils.aoa_to_sheet(aoa);
        wsSummary['!cols'] = [{ wch: 28 }, { wch: 22 }];
        XLSX.utils.book_append_sheet(wb, wsSummary, 'Summary');

        // Records sheet
        const header = ['#', 'ID Number', 'Full Name', 'RSBSA', 'Barangay', 'Sex',
            'Date of Birth', 'Age', 'Contact Number'].concat(CATEGORY_KEYS.map(k => CATEGORY_LABELS[k]));
        const rows = data.map((it, i) => {
            const age = calcAge(it.date_of_birth);
            return [
                i + 1,
                it.id_number || '',
                it.full_name || '',
                it.rsbsa || '',
                it.address || '',
                it.sex || '',
                it.date_of_birth || '',
                age === null ? '' : age,
                it.contact_number || ''
            ].concat(CATEGORY_KEYS.map(k => (String(it[k]) === '1' ? 'Yes' : 'No')));
        });
        const wsRecords = XLSX.utils.aoa_to_sheet([header].concat(rows));
        wsRecords['!cols'] = [
            { wch: 5 }, { wch: 22 }, { wch: 30 }, { wch: 24 }, { wch: 18 }, { wch: 8 },
            { wch: 13 }, { wch: 6 }, { wch: 15 }
        ].concat(CATEGORY_KEYS.map(() => ({ wch: 18 })));
        wsRecords['!freeze'] = { xSplit: 0, ySplit: 1 };
        XLSX.utils.book_append_sheet(wb, wsRecords, 'Fisherfolk');

        XLSX.writeFile(wb, ctx.fileBase + '.xlsx');
    }

    // ---- wire up ---------------------------------------------------------

    function init() {
        const pdfBtn = document.getElementById('exportPdfBtn');
        const xlsBtn = document.getElementById('exportExcelBtn');
        if (pdfBtn) pdfBtn.addEventListener('click', () => withBusy(pdfBtn, exportPDF));
        if (xlsBtn) xlsBtn.addEventListener('click', () => withBusy(xlsBtn, exportExcel));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
