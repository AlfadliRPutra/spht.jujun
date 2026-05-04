{{-- Helper JS: akumulasi pilihan file multi-batch untuk <input type="file" multiple>.
     Browser default mengganti seluruh selection setiap kali file picker dibuka,
     jadi kita simpan File[] sendiri dan menulis ulang input.files via DataTransfer.

     Pakai:
         window.spht_setupExtraImages(inputElement, {
             previewEl: <containerElementForThumbs>,
             maxFiles: 5,
             maxBytes: 4 * 1024 * 1024,        // opsional, default 4 MB
             allowed:  ['image/jpeg', ...],   // opsional
         });

     File picker akan menambah ke selection sebelumnya. Tiap thumbnail punya
     tombol hapus (X). Validasi tipe/ukuran/jumlah dilakukan saat menambah,
     dengan banner error sementara di atas grid preview. --}}
<script>
    (function () {
        if (window.spht_setupExtraImages) return;

        window.spht_setupExtraImages = function (input, options) {
            if (! input) return;
            options = options || {};
            const preview  = options.previewEl;
            if (! preview) return;

            const MAX_FILES = options.maxFiles ?? 5;
            const MAX_BYTES = options.maxBytes ?? (4 * 1024 * 1024);
            const ALLOWED   = options.allowed  ?? ['image/jpeg', 'image/png', 'image/webp'];

            const store     = [];      // File[]
            const previewUrls = new WeakMap();

            const sync = () => {
                const dt = new DataTransfer();
                store.forEach(f => dt.items.add(f));
                input.files = dt.files;
                render();
            };

            const render = () => {
                preview.innerHTML = '';

                const counter = document.createElement('div');
                counter.className = 'small text-secondary w-100 mb-1';
                counter.innerHTML = '<i class="ti ti-photo me-1"></i>'
                    + store.length + '/' + MAX_FILES + ' foto dipilih'
                    + (store.length === MAX_FILES ? ' (slot penuh)' : '') + '.';
                preview.appendChild(counter);

                store.forEach((file, idx) => {
                    let url = previewUrls.get(file);
                    if (! url) {
                        url = URL.createObjectURL(file);
                        previewUrls.set(file, url);
                    }

                    const chip = document.createElement('div');
                    chip.className = 'rounded border position-relative';
                    chip.style.cssText = 'width:88px;height:88px;overflow:hidden;background:#f6f8fa';

                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = file.name;
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block';
                    chip.appendChild(img);

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.title = 'Hapus foto ini';
                    btn.className = 'position-absolute';
                    btn.style.cssText = 'top:2px;right:2px;width:22px;height:22px;border:0;border-radius:50%;'
                        + 'background:rgba(220,38,38,.92);color:#fff;display:inline-flex;'
                        + 'align-items:center;justify-content:center;line-height:1;font-size:.85rem;cursor:pointer';
                    btn.innerHTML = '<i class="ti ti-x"></i>';
                    btn.addEventListener('click', () => {
                        store.splice(idx, 1);
                        sync();
                    });
                    chip.appendChild(btn);

                    preview.appendChild(chip);
                });
            };

            const flashError = (msg) => {
                let banner = preview.querySelector('.js-extra-err');
                if (! banner) {
                    banner = document.createElement('div');
                    banner.className = 'js-extra-err small text-danger w-100 mt-1';
                    preview.appendChild(banner);
                }
                banner.innerHTML = '<i class="ti ti-alert-circle me-1"></i>' + msg;
                clearTimeout(flashError._t);
                flashError._t = setTimeout(() => banner.remove(), 4000);
            };

            input.addEventListener('change', () => {
                input.classList.remove('is-invalid');
                const picked = Array.from(input.files || []);

                for (const f of picked) {
                    if (store.length >= MAX_FILES) {
                        flashError('Maksimal ' + MAX_FILES + ' foto, sisanya diabaikan.');
                        break;
                    }
                    if (! ALLOWED.includes(f.type)) {
                        flashError('"' + f.name + '" bukan JPG/PNG/WEBP, dilewati.');
                        continue;
                    }
                    if (f.size > MAX_BYTES) {
                        flashError('"' + f.name + '" melebihi ' + (MAX_BYTES / 1024 / 1024) + ' MB, dilewati.');
                        continue;
                    }
                    // Cegah duplikat berdasarkan nama + ukuran (heuristik cukup
                    // untuk file yang sama dipilih ulang dari folder sama).
                    if (store.some(s => s.name === f.name && s.size === f.size)) {
                        continue;
                    }
                    store.push(f);
                }

                sync();
            });

            // Inisialisasi dengan store kosong → input.files juga kosong.
            sync();
        };
    })();
</script>
