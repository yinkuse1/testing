<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PDF Merge & Preview</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800">
  <main class="max-w-5xl mx-auto p-6">
    <header class="mb-6">
      <h1 class="text-2xl md:text-3xl font-bold">Upload a PDF, add a comment, merge it, then preview</h1>
      <p class="text-sm text-slate-600 mt-1">Client-side only � No files leave your browser</p>
    </header>

    <!-- Uploader & Controls -->
    <section class="grid md:grid-cols-2 gap-6 items-start">
      <div class="space-y-4 bg-white p-5 rounded-2xl shadow">
        <div>
          <label class="block text-sm font-medium mb-1">Select PDF</label>
          <input id="pdfInput" type="file" accept="application/pdf" class="block w-full file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-700" />
          <p id="fileInfo" class="text-xs text-slate-500 mt-1"></p>
        </div>

        <div>
          <label for="comment" class="block text-sm font-medium mb-1">Comment text</label>
          <textarea id="comment" rows="4" placeholder="Type your comment to stamp onto the PDF�" class="w-full rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-slate-400"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Font size</label>
            <input id="fontSize" type="number" min="8" max="72" value="14" class="w-full rounded-xl border border-slate-300 p-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Page to apply</label>
            <input id="pageIndex" type="number" min="1" value="1" class="w-full rounded-xl border border-slate-300 p-2" />
            <p class="text-[11px] text-slate-500 mt-1">1 = first page</p>
          </div>
        </div>

        <details class="rounded-xl border p-3">
          <summary class="cursor-pointer text-sm font-medium">Placement options</summary>
          <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div>
              <label class="block mb-1">X (from left, pt)</label>
              <input id="posX" type="number" value="40" class="w-full rounded-xl border border-slate-300 p-2" />
            </div>
            <div>
              <label class="block mb-1">Y (from bottom, pt)</label>
              <input id="posY" type="number" value="760" class="w-full rounded-xl border border-slate-300 p-2" />
            </div>
            <div>
              <label class="block mb-1">Max width (pt)</label>
              <input id="maxWidth" type="number" value="520" class="w-full rounded-xl border border-slate-300 p-2" />
            </div>
            <div>
              <label class="block mb-1">Line height (pt)</label>
              <input id="lineHeight" type="number" value="18" class="w-full rounded-xl border border-slate-300 p-2" />
            </div>
          </div>
        </details>

        <div class="flex items-center gap-3">
          <button id="mergeBtn" class="px-4 py-2 rounded-2xl bg-slate-900 text-white font-semibold disabled:opacity-40">Merge comment & Preview</button>
          <a id="downloadLink" download="commented.pdf" class="px-4 py-2 rounded-2xl border font-semibold hover:bg-slate-100 hidden">Download merged PDF</a>
        </div>
        <p id="status" class="text-sm text-slate-600"></p>
      </div>

      <!-- Preview Panel -->
      <div class="bg-white p-3 rounded-2xl shadow h-[80vh]">
        <iframe id="preview" title="Merged PDF Preview" class="w-full h-full rounded-xl border"></iframe>
      </div>
    </section>
  </main>

  <script>
    const pdfInput = document.getElementById('pdfInput');
    const fileInfo = document.getElementById('fileInfo');
    const commentEl = document.getElementById('comment');
    const fontSizeEl = document.getElementById('fontSize');
    const pageIndexEl = document.getElementById('pageIndex');
    const posXEl = document.getElementById('posX');
    const posYEl = document.getElementById('posY');
    const maxWidthEl = document.getElementById('maxWidth');
    const lineHeightEl = document.getElementById('lineHeight');
    const mergeBtn = document.getElementById('mergeBtn');
    const downloadLink = document.getElementById('downloadLink');
    const preview = document.getElementById('preview');
    const status = document.getElementById('status');

    let originalArrayBuffer = null;

    pdfInput.addEventListener('change', async (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      fileInfo.textContent = `${file.name} � ${(file.size / 1024 / 1024).toFixed(2)} MB`;
      originalArrayBuffer = await file.arrayBuffer();
      status.textContent = 'PDF loaded. Add your comment, then click Merge comment & Preview.';
      downloadLink.classList.add('hidden');
      preview.src = URL.createObjectURL(new Blob([originalArrayBuffer], { type: 'application/pdf' }));
    });

    function wrapText(text, maxWidth, font, size) {
      const words = text.split(/\s+/);
      const lines = [];
      let current = '';

      for (const word of words) {
        const test = current ? current + ' ' + word : word;
        const w = font.widthOfTextAtSize(test, size);
        if (w <= maxWidth) {
          current = test;
        } else {
          if (current) lines.push(current);
          current = word;
        }
      }
      if (current) lines.push(current);
      return lines;
    }

    mergeBtn.addEventListener('click', async () => {
      try {
        if (!originalArrayBuffer) {
          alert('Please choose a PDF first.');
          return;
        }
        const comment = commentEl.value.trim();
        if (!comment) {
          alert('Please enter a comment.');
          return;
        }

        status.textContent = 'Merging comment into PDF�';

        const { PDFDocument, StandardFonts, rgb } = PDFLib;
        const pdfDoc = await PDFDocument.load(originalArrayBuffer);

        // Clamp page index (1-based input to 0-based)
        let pageIndex = Math.max(1, parseInt(pageIndexEl.value || '1', 10));
        pageIndex = Math.min(pageIndex, pdfDoc.getPageCount());
        const page = pdfDoc.getPage(pageIndex - 1);

        const fontSize = Math.max(8, Math.min(72, parseFloat(fontSizeEl.value || '14')));
        const x = parseFloat(posXEl.value || '40');
        let y = parseFloat(posYEl.value || '760');
        const maxWidth = Math.max(50, parseFloat(maxWidthEl.value || '520'));
        const lineHeight = Math.max(10, parseFloat(lineHeightEl.value || '18'));

        const font = await pdfDoc.embedFont(StandardFonts.Helvetica);
        const lines = wrapText(comment, maxWidth, font, fontSize);

        for (const line of lines) {
          page.drawText(line, {
            x,
            y,
            size: fontSize,
            font,
            color: rgb(0, 0, 0),
          });
          y -= lineHeight;
        }

        const stampedBytes = await pdfDoc.save();
        const blob = new Blob([stampedBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        preview.src = url;
        downloadLink.href = url;
        downloadLink.classList.remove('hidden');
        status.textContent = 'Done. Preview updated below. You can also download the merged PDF.';
      } catch (err) {
        console.error(err);
        status.textContent = 'Something went wrong while merging. Check the console for details.';
        alert('Failed to merge comment. See console for details.');
      }
    });
  </script>
</body>
</html>
