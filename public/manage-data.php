<?php
require_once __DIR__ . '/../config/auth-functions.php';
require_page_auth();
$currentUser = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Data &amp; Assets | Calapan City FMO</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#F28500', accent: '#0000FF' } } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- SheetJS: parse .xlsx and .csv in the browser (already used by the dashboard export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        body { font-family: 'Roboto', sans-serif; }
        .drop-zone { border: 3px dashed #0000FF; transition: all .2s; }
        .drop-zone.dragover { background:#eef2ff; border-color:#F28500; }
        ::-webkit-scrollbar { width:8px; height:8px; }
        ::-webkit-scrollbar-thumb { background:#F28500; border-radius:4px; }
    </style>
</head>
<body class="bg-gray-50 pb-24">

<!-- Header -->
<nav class="bg-gradient-to-r from-primary to-orange-600 shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <span class="text-white text-xl font-bold">
            <i class="fas fa-database"></i> Manage Data &amp; Assets
        </span>
        <div class="flex items-center gap-3">
            <a href="/" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <span class="text-white text-sm hidden sm:inline"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser); ?></span>
            <a href="logout.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mx-auto px-4 py-6 max-w-5xl">

    <!-- Step indicator -->
    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-lg p-4 mb-6 text-sm">
        <p class="font-semibold mb-1"><i class="fas fa-route mr-1"></i> Recommended order</p>
        <ol class="list-decimal ml-5 space-y-0.5">
            <li><strong>Upload the data file</strong> (Excel/CSV) &mdash; records and the photo/signature <em>filenames</em> they reference.</li>
            <li><strong>Upload the photo &amp; signature image files</strong> &mdash; they are matched to records by filename.</li>
            <li><strong>Fix individual missing assets</strong> &mdash; upload a single photo or signature for one person.</li>
        </ol>
    </div>

    <!-- ============ STEP 1: DATA ============ -->
    <section class="bg-white rounded-lg shadow-md mb-6">
        <div class="bg-primary text-white px-5 py-3 rounded-t-lg flex items-center gap-2">
            <span class="bg-white text-primary font-bold rounded-full w-7 h-7 flex items-center justify-center">1</span>
            <h2 class="text-lg font-bold"><i class="fas fa-file-import mr-1"></i> Upload Data File (Excel / CSV)</h2>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">
                Accepts the FMO <strong>Master List</strong> workbook (<code>.xlsx</code>) or the
                <a href="/downloads/fisherfolk_template.csv" download class="text-accent underline">CSV template</a>.
                Columns are detected by their header names. The file is parsed in your browser; nothing is saved until you click <em>Import</em>.
            </p>

            <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
                <div>
                    <span class="font-semibold mr-2">Date format in file:</span>
                    <label class="mr-3"><input type="radio" name="dateFmt" value="mdy" checked> MM/DD/YYYY <span class="text-gray-400">(FMO masterlist)</span></label>
                    <label><input type="radio" name="dateFmt" value="dmy"> DD/MM/YYYY <span class="text-gray-400">(template)</span></label>
                </div>
                <label class="inline-flex items-center gap-2 ml-auto">
                    <input type="checkbox" id="updateExisting" class="w-4 h-4">
                    <span>Update records that already exist (otherwise they're skipped)</span>
                </label>
            </div>

            <div id="dataDrop" class="drop-zone rounded-lg p-10 text-center cursor-pointer bg-gray-50">
                <i class="fas fa-cloud-upload-alt text-5xl text-accent mb-3"></i>
                <p class="font-semibold text-gray-700">Drag &amp; drop the .xlsx / .csv here, or click to browse</p>
                <input type="file" id="dataFile" accept=".xlsx,.xls,.csv" class="hidden">
            </div>

            <div id="dataPreview" class="mt-4 hidden">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-700">Preview &mdash; <span id="dataCount">0</span> records (normalized)</h3>
                    <span id="dataSheet" class="text-xs text-gray-500"></span>
                </div>
                <div class="overflow-auto border rounded max-h-96 text-xs">
                    <table class="min-w-full">
                        <thead class="bg-gray-100 sticky top-0"><tr id="dataHead"></tr></thead>
                        <tbody id="dataBody"></tbody>
                    </table>
                </div>
                <button id="dataImportBtn" class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">
                    <i class="fas fa-database mr-1"></i> Import Data to Database
                </button>
            </div>

            <div id="dataResult" class="mt-4 hidden"></div>
        </div>
    </section>

    <!-- ============ STEP 2: BULK ASSETS ============ -->
    <section class="bg-white rounded-lg shadow-md mb-6">
        <div class="bg-primary text-white px-5 py-3 rounded-t-lg flex items-center gap-2">
            <span class="bg-white text-primary font-bold rounded-full w-7 h-7 flex items-center justify-center">2</span>
            <h2 class="text-lg font-bold"><i class="fas fa-images mr-1"></i> Upload Photos &amp; Signatures (bulk)</h2>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">
                Select all the photo and signature image files. Each file is matched to a record by its
                <strong>filename</strong> (the name written in the Image / Signature column, e.g.
                <code>MR-CL-001143-2015.JPG</code>). Existing files with the same name are
                <strong>replaced</strong>. Large batches are uploaded automatically in chunks.
            </p>

            <div id="assetDrop" class="drop-zone rounded-lg p-10 text-center cursor-pointer bg-gray-50">
                <i class="fas fa-photo-film text-5xl text-accent mb-3"></i>
                <p class="font-semibold text-gray-700">Drag &amp; drop image files here, or click to browse</p>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP &mdash; up to 10 MB each</p>
                <input type="file" id="assetFiles" accept="image/*" multiple class="hidden">
            </div>

            <div id="assetStaged" class="mt-3 text-sm text-gray-700 hidden">
                <span id="assetStagedCount">0</span> file(s) selected.
                <button id="assetUploadBtn" class="ml-3 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-1.5 rounded">
                    <i class="fas fa-upload mr-1"></i> Upload &amp; Match
                </button>
            </div>

            <div id="assetProgress" class="mt-4 hidden">
                <div class="w-full bg-gray-200 rounded-full h-5 overflow-hidden">
                    <div id="assetBar" class="bg-primary h-5 text-xs text-white text-center leading-5" style="width:0%">0%</div>
                </div>
            </div>

            <div id="assetResult" class="mt-4 hidden"></div>
        </div>
    </section>

    <!-- ============ STEP 3: SINGLE ASSET ============ -->
    <section class="bg-white rounded-lg shadow-md mb-6">
        <div class="bg-primary text-white px-5 py-3 rounded-t-lg flex items-center gap-2">
            <span class="bg-white text-primary font-bold rounded-full w-7 h-7 flex items-center justify-center">3</span>
            <h2 class="text-lg font-bold"><i class="fas fa-user-pen mr-1"></i> Fix a Single Missing Photo / Signature</h2>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">Search the person, choose Photo or Signature, then upload one image. It replaces any existing one.</p>

            <div class="relative mb-3">
                <input id="ffSearch" type="text" placeholder="Search by name or ID number…"
                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                <div id="ffResults" class="absolute z-20 left-0 right-0 bg-white border rounded-lg shadow-lg mt-1 max-h-72 overflow-auto hidden"></div>
            </div>

            <div id="ffSelected" class="hidden border rounded-lg p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-bold text-gray-800" id="selName"></p>
                        <p class="text-sm text-gray-500" id="selMeta"></p>
                    </div>
                    <div class="text-sm" id="selAssets"></div>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <label class="font-semibold text-sm">Asset type:</label>
                    <label><input type="radio" name="assetType" value="image" checked> Photo</label>
                    <label><input type="radio" name="assetType" value="signature"> Signature</label>
                    <input type="file" id="singleFile" accept="image/*" class="text-sm">
                    <button id="singleUploadBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-1.5 rounded">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
                <div id="singleResult" class="mt-3 text-sm"></div>
            </div>
        </div>
    </section>

</div>

<footer class="fixed bottom-0 inset-x-0 bg-white border-t text-center text-xs text-gray-500 py-2">
    Powered by <strong>Powerbyte IT Solutions</strong> &middot; &copy; <?php echo date('Y'); ?> Calapan City FMO
</footer>

<script>
// ---------------------------------------------------------------------------
// Header synonyms -> canonical field
// ---------------------------------------------------------------------------
const HEADER_MAP = {
    id_number: ['id_number','id','id number','idnumber','fishr id','fishr id number','control number','control no','registration number'],
    full_name: ['full_name','name','full name','fisherfolk name','complete name'],
    date_of_birth: ['date_of_birth','dob','date of birth','birthdate','birth date','birthday'],
    address: ['address','barangay','brgy','barangay/address'],
    sex: ['sex','gender'],
    image: ['image','photo','picture','photo filename','image filename','photo file','image file'],
    signature: ['signature','sign','signature filename','signature file','specimen signature'],
    rsbsa: ['rsbsa','rsbsa number','rsbsa no','rsbsa no.'],
    contact_number: ['contact_number','contact','contact number','contact no','mobile','mobile number','cellphone','phone','phone number','cp number'],
    category: ['category','categories','type','classification','activity','livelihood','sector'],
    boat_owneroperator: ['boat_owneroperator','boat owner','boat owner/operator','boat owner operator'],
    capture_fishing: ['capture_fishing','capture fishing','capture'],
    gleaning: ['gleaning','gleaner'],
    vendor: ['vendor','vending'],
    fish_processing: ['fish_processing','fish processing','processing'],
    aquaculture: ['aquaculture'],
};
const FLAGS = ['boat_owneroperator','capture_fishing','gleaning','vendor','fish_processing','aquaculture'];
const PREVIEW_COLS = ['id_number','full_name','date_of_birth','address','sex','image','signature','rsbsa','contact_number', ...FLAGS];

function canonicalKey(header) {
    const h = String(header || '').trim().toLowerCase().replace(/\s+/g,' ').replace(/[._]+/g,' ').trim();
    for (const [canon, syns] of Object.entries(HEADER_MAP)) {
        if (syns.some(s => s.replace(/[._]+/g,' ') === h)) return canon;
    }
    return null;
}

// ---------------------------------------------------------------------------
// Normalizers (mirror tools/import_masterlist.py)
// ---------------------------------------------------------------------------
const ROMAN = new Set(['i','ii','iii','iv','v','vi','vii','viii','ix','x']);

function pad(n){ return String(n).padStart(2,'0'); }

function normDob(value, fmt) {
    if (value === null || value === undefined || value === '') return null;
    if (value instanceof Date && !isNaN(value)) {
        return value.getFullYear() + '-' + pad(value.getMonth()+1) + '-' + pad(value.getDate());
    }
    const s = String(value).trim();
    const m = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/);
    if (m) {
        let a = parseInt(m[1],10), b = parseInt(m[2],10), y = parseInt(m[3],10);
        if (y < 100) y += (y > 30 ? 1900 : 2000);
        let mm, dd;
        if (a > 12 && b <= 12) { dd = a; mm = b; }            // unambiguous DD/MM
        else if (b > 12 && a <= 12) { mm = a; dd = b; }       // unambiguous MM/DD
        else { if (fmt === 'dmy') { dd = a; mm = b; } else { mm = a; dd = b; } }
        if (mm < 1 || mm > 12 || dd < 1 || dd > 31) return null;
        return y + '-' + pad(mm) + '-' + pad(dd);
    }
    const d = new Date(s);
    if (!isNaN(d)) return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
    return null;
}

function normSex(v) {
    const s = (v == null ? '' : String(v).trim().toLowerCase());
    if (s.startsWith('m')) return 'Male';
    if (s.startsWith('f')) return 'Female';
    return '';
}

function normBarangay(v) {
    if (!v) return '';
    let s = String(v).split(',')[0].replace(/\s+/g,' ').trim().toLowerCase();
    // Match Python str.title(): capitalize the first letter of every run of
    // letters (so "nag-iba" -> "Nag-Iba", "sto. niño" -> "Sto. Niño").
    s = s.replace(/(^|[^a-zà-ÿ])([a-zà-ÿ])/g, (m, p, c) => p + c.toUpperCase());
    // Keep Roman-numeral suffixes fully uppercase ("Ii" -> "II").
    return s.split(' ').map(w => ROMAN.has(w.toLowerCase()) ? w.toUpperCase() : w).join(' ');
}

function normContact(v) {
    if (v == null || v === '') return null;
    let d = String(v).replace(/\D/g,'');
    if (!d) return null;
    if (d.length === 10 && d.startsWith('9')) d = '0' + d;
    return d;
}

function categoryFlags(text) {
    const s = (text == null ? '' : String(text).toLowerCase());
    return {
        boat_owneroperator: s.includes('boat owner') ? 1 : 0,
        capture_fishing: s.includes('capture fish') ? 1 : 0,
        gleaning: (s.includes('gleaning') || s.includes('gleaner')) ? 1 : 0,
        vendor: s.includes('vend') ? 1 : 0,
        fish_processing: s.includes('processing') ? 1 : 0,
        aquaculture: s.includes('aquaculture') ? 1 : 0,
    };
}

function flagFromCell(v) {
    const s = String(v == null ? '' : v).trim().toLowerCase();
    return (s === '1' || s === 'yes' || s === 'y' || s === 'true' || s === 'x' || s === '✓') ? 1 : 0;
}

// ---------------------------------------------------------------------------
// Parse workbook / csv -> normalized rows
// ---------------------------------------------------------------------------
let parsedRows = [];

function buildColIndex(headerRow) {
    const idx = {};
    headerRow.forEach((h, i) => {
        const key = canonicalKey(h);
        if (key && !(key in idx)) idx[key] = i;
    });
    return idx;
}

function normalizeRow(arr, idx, fmt) {
    const get = k => (k in idx) ? arr[idx[k]] : undefined;
    const row = {
        id_number: get('id_number') != null ? String(get('id_number')).trim() : '',
        full_name: get('full_name') != null ? String(get('full_name')).trim() : '',
        date_of_birth: normDob(get('date_of_birth'), fmt),
        address: normBarangay(get('address')),
        sex: normSex(get('sex')),
        image: get('image') != null && String(get('image')).trim() !== '' ? String(get('image')).trim() : null,
        signature: get('signature') != null && String(get('signature')).trim() !== '' ? String(get('signature')).trim() : null,
        rsbsa: get('rsbsa') != null && String(get('rsbsa')).trim() !== '' ? String(get('rsbsa')).trim() : null,
        contact_number: normContact(get('contact_number')),
    };
    // Activity flags: explicit boolean columns win; else parse free-text CATEGORY.
    const hasExplicit = FLAGS.some(f => f in idx);
    if (hasExplicit) {
        FLAGS.forEach(f => { row[f] = (f in idx) ? flagFromCell(arr[idx[f]]) : 0; });
    } else {
        Object.assign(row, categoryFlags(get('category')));
    }
    return row;
}

function handleDataFile(file) {
    const fmt = document.querySelector('input[name="dateFmt"]:checked').value;
    const reader = new FileReader();
    reader.onload = (e) => {
        let wb;
        try {
            wb = XLSX.read(e.target.result, { type: 'array', cellDates: true });
        } catch (err) {
            alert('Could not read file: ' + err.message);
            return;
        }
        // Prefer a sheet named like "Master List", else first sheet.
        let sheetName = wb.SheetNames.find(n => /master\s*list/i.test(n)) || wb.SheetNames[0];
        const ws = wb.Sheets[sheetName];
        const aoa = XLSX.utils.sheet_to_json(ws, { header: 1, raw: false, defval: '' });
        if (!aoa.length) { alert('The sheet is empty.'); return; }

        const headerRow = aoa[0];
        const idx = buildColIndex(headerRow);
        if (!('id_number' in idx) || !('full_name' in idx)) {
            alert('Could not find the ID and Name columns. Detected headers: ' + headerRow.join(', '));
            return;
        }
        parsedRows = [];
        for (let i = 1; i < aoa.length; i++) {
            const arr = aoa[i];
            if (!arr || arr.every(c => c === '' || c == null)) continue;
            const r = normalizeRow(arr, idx, fmt);
            if (!r.id_number && !r.full_name) continue;
            parsedRows.push(r);
        }
        document.getElementById('dataSheet').textContent = 'Sheet: ' + sheetName;
        renderDataPreview();
    };
    reader.readAsArrayBuffer(file);
}

function esc(s){ return String(s==null?'':s).replace(/[&<>]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;' }[c])); }

function renderDataPreview() {
    document.getElementById('dataCount').textContent = parsedRows.length;
    document.getElementById('dataHead').innerHTML = PREVIEW_COLS.map(c => `<th class="px-2 py-1 text-left font-semibold border-b whitespace-nowrap">${c}</th>`).join('');
    const body = document.getElementById('dataBody');
    body.innerHTML = parsedRows.slice(0,15).map(r =>
        '<tr class="odd:bg-white even:bg-gray-50">' + PREVIEW_COLS.map(c => `<td class="px-2 py-1 border-b whitespace-nowrap">${esc(r[c])}</td>`).join('') + '</tr>'
    ).join('');
    if (parsedRows.length > 15) {
        body.innerHTML += `<tr><td colspan="${PREVIEW_COLS.length}" class="px-2 py-2 text-center text-gray-400">… and ${parsedRows.length-15} more</td></tr>`;
    }
    document.getElementById('dataPreview').classList.remove('hidden');
    document.getElementById('dataResult').classList.add('hidden');
}

document.getElementById('dataImportBtn').addEventListener('click', async () => {
    if (!parsedRows.length) { alert('Nothing to import.'); return; }
    const updateExisting = document.getElementById('updateExisting').checked;
    if (!confirm(`Import ${parsedRows.length} records?` + (updateExisting ? '\nExisting records WILL be updated.' : '\nExisting records will be skipped.'))) return;
    const btn = document.getElementById('dataImportBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Importing…';
    try {
        const res = await fetch('/api/bulk-import-data.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ data: parsedRows, update_existing: updateExisting })
        });
        const j = await res.json();
        const box = document.getElementById('dataResult');
        box.classList.remove('hidden');
        if (j.success) {
            box.innerHTML = `<div class="bg-green-50 border border-green-300 text-green-800 rounded p-4">
                <p class="font-bold"><i class="fas fa-check-circle"></i> Import complete</p>
                <p>Inserted: <strong>${j.inserted}</strong> &middot; Updated: <strong>${j.updated}</strong> &middot; Skipped: <strong>${j.skipped}</strong> (of ${j.total})</p>
                ${j.errors && j.errors.length ? `<details class="mt-2"><summary>${j.errors.length} warning(s)</summary><ul class="list-disc ml-5 text-sm">${j.errors.slice(0,20).map(e=>`<li>${esc(e)}</li>`).join('')}</ul></details>` : ''}
            </div>`;
            document.getElementById('dataPreview').classList.add('hidden');
        } else {
            box.innerHTML = `<div class="bg-red-50 border border-red-300 text-red-800 rounded p-4"><i class="fas fa-times-circle"></i> ${esc(j.error)}</div>`;
        }
    } catch (err) {
        document.getElementById('dataResult').classList.remove('hidden');
        document.getElementById('dataResult').innerHTML = `<div class="bg-red-50 border border-red-300 text-red-800 rounded p-4">${esc(err.message)}</div>`;
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-database mr-1"></i> Import Data to Database';
    }
});

// ---------------------------------------------------------------------------
// Generic drop-zone wiring
// ---------------------------------------------------------------------------
function wireDrop(zoneId, inputId, onFiles) {
    const zone = document.getElementById(zoneId), input = document.getElementById(inputId);
    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('dragover'); onFiles(e.dataTransfer.files); });
    input.addEventListener('change', e => onFiles(e.target.files));
}

wireDrop('dataDrop','dataFile', files => { if (files.length) handleDataFile(files[0]); });

// ---------------------------------------------------------------------------
// STEP 2: bulk asset upload (chunked)
// ---------------------------------------------------------------------------
let stagedAssets = [];
const CHUNK = 15; // stay under PHP max_file_uploads (default 20)

wireDrop('assetDrop','assetFiles', files => {
    stagedAssets = Array.from(files).filter(f => f.type.startsWith('image/') || /\.(jpe?g|png|gif|webp)$/i.test(f.name));
    document.getElementById('assetStagedCount').textContent = stagedAssets.length;
    document.getElementById('assetStaged').classList.toggle('hidden', stagedAssets.length === 0);
    document.getElementById('assetResult').classList.add('hidden');
});

document.getElementById('assetUploadBtn').addEventListener('click', async () => {
    if (!stagedAssets.length) return;
    const bar = document.getElementById('assetBar');
    document.getElementById('assetProgress').classList.remove('hidden');
    const agg = { total:0, matched:0, unmatched:0, replaced:0, failed:0, results:[] };

    for (let i = 0; i < stagedAssets.length; i += CHUNK) {
        const slice = stagedAssets.slice(i, i+CHUNK);
        const fd = new FormData();
        slice.forEach(f => fd.append('files[]', f, f.name));
        try {
            const res = await fetch('/api/upload-assets.php', { method:'POST', body: fd });
            const j = await res.json();
            if (j.success) {
                for (const k of ['total','matched','unmatched','replaced','failed']) agg[k] += j.summary[k];
                agg.results.push(...j.results);
            } else {
                agg.failed += slice.length;
                agg.results.push(...slice.map(f => ({file:f.name, status:'failed', reason:j.error, matched_ids:[]})));
            }
        } catch (err) {
            agg.failed += slice.length;
            agg.results.push(...slice.map(f => ({file:f.name, status:'failed', reason:err.message, matched_ids:[]})));
        }
        const pct = Math.round(Math.min(i+CHUNK, stagedAssets.length) / stagedAssets.length * 100);
        bar.style.width = pct + '%'; bar.textContent = pct + '%';
    }

    const unmatchedList = agg.results.filter(r => r.status === 'unmatched');
    const failedList = agg.results.filter(r => r.status === 'failed');
    const box = document.getElementById('assetResult');
    box.classList.remove('hidden');
    box.innerHTML = `<div class="bg-green-50 border border-green-300 text-green-800 rounded p-4">
        <p class="font-bold"><i class="fas fa-check-circle"></i> Upload complete</p>
        <p>Matched to records: <strong>${agg.matched}</strong> &middot; Replaced existing: <strong>${agg.replaced}</strong>
           &middot; Unmatched: <strong>${agg.unmatched}</strong> &middot; Failed: <strong>${agg.failed}</strong> (of ${agg.total})</p>
        ${unmatchedList.length ? `<details class="mt-2"><summary>${unmatchedList.length} unmatched file(s) (saved, but no record references them)</summary>
            <ul class="list-disc ml-5 text-sm max-h-40 overflow-auto">${unmatchedList.slice(0,100).map(r=>`<li>${esc(r.file)}</li>`).join('')}</ul></details>` : ''}
        ${failedList.length ? `<details class="mt-2 text-red-700"><summary>${failedList.length} failed</summary>
            <ul class="list-disc ml-5 text-sm max-h-40 overflow-auto">${failedList.slice(0,100).map(r=>`<li>${esc(r.file)} — ${esc(r.reason||'')}</li>`).join('')}</ul></details>` : ''}
    </div>`;
    stagedAssets = [];
    document.getElementById('assetStaged').classList.add('hidden');
});

// ---------------------------------------------------------------------------
// STEP 3: single asset fix
// ---------------------------------------------------------------------------
let selectedId = null;
let searchTimer = null;
const ffSearch = document.getElementById('ffSearch');
const ffResults = document.getElementById('ffResults');

ffSearch.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = ffSearch.value.trim();
    if (q.length < 2) { ffResults.classList.add('hidden'); return; }
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch('/api/fisherfolk-search.php?q=' + encodeURIComponent(q));
            const j = await res.json();
            if (!j.success || !j.data.length) {
                ffResults.innerHTML = '<div class="px-4 py-2 text-gray-400 text-sm">No matches</div>';
            } else {
                ffResults.innerHTML = j.data.map(r => `
                    <div class="px-4 py-2 hover:bg-orange-50 cursor-pointer border-b text-sm"
                         onclick='selectFisherfolk(${JSON.stringify(r).replace(/'/g,"&#39;")})'>
                        <span class="font-semibold">${esc(r.full_name)}</span>
                        <span class="text-gray-500">— ${esc(r.id_number)} · ${esc(r.address||'')}</span>
                        <span class="ml-1">${r.has_image?'':'<span class=\"text-red-500\">no photo</span>'} ${r.has_signature?'':'<span class=\"text-red-500\">no sig</span>'}</span>
                    </div>`).join('');
            }
            ffResults.classList.remove('hidden');
        } catch (_) { ffResults.classList.add('hidden'); }
    }, 250);
});

document.addEventListener('click', e => {
    if (!ffResults.contains(e.target) && e.target !== ffSearch) ffResults.classList.add('hidden');
});

function selectFisherfolk(r) {
    selectedId = r.id_number;
    ffResults.classList.add('hidden');
    ffSearch.value = r.full_name + ' (' + r.id_number + ')';
    document.getElementById('selName').textContent = r.full_name;
    document.getElementById('selMeta').textContent = r.id_number + ' · ' + (r.address || '');
    document.getElementById('selAssets').innerHTML =
        `Photo: ${r.has_image?'<span class="text-green-600">present</span>':'<span class="text-red-500">missing</span>'} &nbsp;|&nbsp; ` +
        `Signature: ${r.has_signature?'<span class="text-green-600">present</span>':'<span class="text-red-500">missing</span>'}`;
    document.getElementById('singleResult').innerHTML = '';
    document.getElementById('ffSelected').classList.remove('hidden');
}
window.selectFisherfolk = selectFisherfolk;

document.getElementById('singleUploadBtn').addEventListener('click', async () => {
    const fileInput = document.getElementById('singleFile');
    if (!selectedId) { alert('Select a fisherfolk first.'); return; }
    if (!fileInput.files.length) { alert('Choose an image file.'); return; }
    const type = document.querySelector('input[name="assetType"]:checked').value;
    const fd = new FormData();
    fd.append('id_number', selectedId);
    fd.append('type', type);
    fd.append('file', fileInput.files[0]);
    const out = document.getElementById('singleResult');
    out.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
    try {
        const res = await fetch('/api/assign-asset.php', { method:'POST', body: fd });
        const j = await res.json();
        if (j.success) {
            out.innerHTML = `<span class="text-green-700"><i class="fas fa-check-circle"></i> ${j.replaced?'Replaced':'Added'} ${j.type} → <code>${esc(j.filename)}</code></span>`;
            fileInput.value = '';
        } else {
            out.innerHTML = `<span class="text-red-600"><i class="fas fa-times-circle"></i> ${esc(j.error)}</span>`;
        }
    } catch (err) {
        out.innerHTML = `<span class="text-red-600">${esc(err.message)}</span>`;
    }
});
</script>
</body>
</html>
