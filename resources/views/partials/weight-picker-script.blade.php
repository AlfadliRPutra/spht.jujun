{{-- Helper JS: picker berat dengan multi-unit (gram, ons, kg, kuintal).
     Setiap blok HTML berat dibungkus class `.js-weight-picker` dengan markup:

         <div class="js-weight-picker">
             <input data-weight-input ...>
             <select data-weight-unit ...> <option value="g|ons|kg|kuintal"> </select>
             <input type="hidden" name="weight_kg" data-weight-kg value="{kg_value}">
             <strong data-weight-preview>—</strong>
         </div>

     User mengisi input + pilih unit, hidden `weight_kg` di-update otomatis
     ke nilai dalam kilogram. Saat edit, hidden value yang sudah ada
     di-dekomposisi ke unit yang paling natural (5 → 5 kg, 0.5 → 5 ons,
     0.05 → 50 g, 500 → 5 kuintal). Backend tetap menerima `weight_kg` saja.

     Cara nambah unit baru (mis. "pon" = 0.4536 kg):
       1. Tambah `<option value="pon">pon</option>` di dropdown view.
       2. Tambah `pon: 0.4536` ke object UNITS di JS di bawah.
       3. Optional: tambah cabang di decompose() supaya nilai existing
          di-pre-select sebagai pon kalau cocok rentangnya. --}}
<script>
    (function () {
        if (window.__sphtWeightPickerInit) return;
        window.__sphtWeightPickerInit = true;

        const UNITS = {
            g:       0.001,
            ons:     0.1,
            kg:      1,
            kuintal: 100,
            // ton:  1000,    // hapus komentar kalau perlu
            // pon:  0.4536,
        };

        const formatKg = (n) => {
            if (! isFinite(n) || n <= 0) return '0';
            return parseFloat(n.toFixed(4)).toString();
        };

        // Pre-select unit yang paling natural untuk value kg existing.
        const decompose = (kg) => {
            if (kg >= 100)  return { value: kg / 100,  unit: 'kuintal' };
            if (kg >= 1)    return { value: kg,        unit: 'kg' };
            if (kg >= 0.1)  return { value: kg * 10,   unit: 'ons' };
            if (kg > 0)     return { value: kg * 1000, unit: 'g' };
            return { value: 1, unit: 'kg' };
        };

        const attach = (wrap) => {
            if (wrap.__bound) return;
            wrap.__bound = true;

            const input   = wrap.querySelector('[data-weight-input]');
            const unitSel = wrap.querySelector('[data-weight-unit]');
            const hidden  = wrap.querySelector('[data-weight-kg]');
            const preview = wrap.querySelector('[data-weight-preview]');
            if (! input || ! unitSel || ! hidden) return;

            const initialKg = parseFloat(hidden.value);
            if (initialKg > 0) {
                const d = decompose(initialKg);
                input.value = parseFloat(d.value.toFixed(4)).toString();
                if ([...unitSel.options].some(o => o.value === d.unit)) {
                    unitSel.value = d.unit;
                }
            }

            const recompute = () => {
                const v = parseFloat(input.value);
                const u = unitSel.value;
                const factor = UNITS[u] ?? 1;
                const kg = (isFinite(v) && v > 0) ? v * factor : 0;
                hidden.value = formatKg(kg);
                if (preview) preview.textContent = formatKg(kg);
            };

            input.addEventListener('input', recompute);
            unitSel.addEventListener('change', recompute);
            recompute();
        };

        const initAll = () => document.querySelectorAll('.js-weight-picker').forEach(attach);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
        // Re-scan setelah modal Bootstrap terbuka (markup mungkin baru diappend ke DOM).
        document.addEventListener('shown.bs.modal', initAll);
    })();
</script>
