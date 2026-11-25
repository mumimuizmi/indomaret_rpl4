/**
 * INDOMARET RPL 4 - SCRIPT SEDERHANA
 */

document.addEventListener('DOMContentLoaded', function () {
    // Konfirmasi Hapus Data
    const deleteLinks = document.querySelectorAll('.confirm-delete');
    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const message = this.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-dismiss Flash Alert setelah 5 detik
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }
        }, 5000);
    });

    // Inisialisasi Form Transaksi
    initTransaksiPOS();
});

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function initTransaksiPOS() {
    const posTable = document.getElementById('cart-table-body');
    if (!posTable) return;

    const productSelect = document.getElementById('product-select');
    const productQty = document.getElementById('product-qty');
    const btnAddToCart = document.getElementById('btn-add-to-cart');
    const donationSelect = document.getElementById('donation-select');
    const cashInput = document.getElementById('cash-input');
    const qrisInput = document.getElementById('qris-input');
    const btnTunaiPas = document.getElementById('btn-tunai-pas');
    const btnQrisPas = document.getElementById('btn-qris-pas');
    const posForm = document.getElementById('pos-form');
    const hiddenTotal = document.getElementById('hidden-total');

    // Tambah produk dari dropdown
    if (btnAddToCart && productSelect) {
        btnAddToCart.addEventListener('click', function () {
            const id = productSelect.value;
            if (!id) {
                alert('Silakan pilih produk terlebih dahulu!');
                productSelect.focus();
                return;
            }
            const opt = productSelect.options[productSelect.selectedIndex];
            const name = opt.getAttribute('data-name') || '';
            const price = parseFloat(opt.getAttribute('data-price')) || 0;
            const discount = parseFloat(opt.getAttribute('data-discount')) || 0;
            const qty = Math.max(1, parseInt(productQty ? productQty.value : 1) || 1);

            tambahProdukKeKeranjang(id, name, price, discount, qty);

            productSelect.selectedIndex = 0;
            if (productQty) productQty.value = 1;
        });
    }

    // Tombol Uang Pas (Tunai)
    if (btnTunaiPas) {
        btnTunaiPas.addEventListener('click', function () {
            const total = parseFloat(hiddenTotal ? hiddenTotal.value : 0) || 0;
            if (cashInput) cashInput.value = total;
            if (qrisInput) qrisInput.value = 0;
            hitungKalkulasiPOS();
        });
    }

    // Tombol QRIS Pas
    if (btnQrisPas) {
        btnQrisPas.addEventListener('click', function () {
            const total = parseFloat(hiddenTotal ? hiddenTotal.value : 0) || 0;
            if (qrisInput) qrisInput.value = total;
            if (cashInput) cashInput.value = 0;
            hitungKalkulasiPOS();
        });
    }

    // Listener Donasi & Pembayaran
    if (donationSelect) {
        donationSelect.addEventListener('change', hitungKalkulasiPOS);
    }
    if (cashInput) {
        cashInput.addEventListener('input', hitungKalkulasiPOS);
    }
    if (qrisInput) {
        qrisInput.addEventListener('input', hitungKalkulasiPOS);
    }

    // Validasi sebelum submit
    if (posForm) {
        posForm.addEventListener('submit', function (e) {
            const rows = posTable.querySelectorAll('tr.cart-row');
            if (rows.length === 0) {
                alert('Daftar belanja masih kosong! Silakan tambahkan minimal 1 produk.');
                e.preventDefault();
                return false;
            }

            const cashierSelect = document.getElementById('cashier-select');
            if (cashierSelect && !cashierSelect.value) {
                alert('Silakan pilih kasir yang bertugas!');
                cashierSelect.focus();
                e.preventDefault();
                return false;
            }

            const total = parseFloat(hiddenTotal ? hiddenTotal.value : 0) || 0;
            const cash = parseFloat(cashInput ? cashInput.value : 0) || 0;
            const qris = parseFloat(qrisInput ? qrisInput.value : 0) || 0;
            const paid = cash + qris;

            if (paid < total) {
                alert('Pembayaran masih kurang! Total: Rp ' + formatUang(total) + ', Dibayar: Rp ' + formatUang(paid));
                if (cashInput) cashInput.focus();
                e.preventDefault();
                return false;
            }
        });
    }

    hitungKalkulasiPOS();
}

function tambahProdukKeKeranjang(id, name, price, discount, qty) {
    const tbody = document.getElementById('cart-table-body');
    const emptyRow = document.getElementById('empty-cart-row');
    if (emptyRow) {
        emptyRow.style.display = 'none';
    }

    // Jika produk sudah ada di tabel, tambah kuantitasnya
    const existingRow = document.getElementById('cart-row-' + id);
    if (existingRow) {
        const qtyInput = existingRow.querySelector('.item-qty');
        qtyInput.value = (parseInt(qtyInput.value) || 0) + qty;
        updateSubtotalBaris(existingRow);
        hitungKalkulasiPOS();
        return;
    }

    const netPrice = Math.max(0, price - discount);
    const subtotal = netPrice * qty;
    const safeName = escapeHtml(name);

    const tr = document.createElement('tr');
    tr.id = 'cart-row-' + id;
    tr.className = 'cart-row';
    tr.innerHTML = `
        <td>
            <span class="fw-semibold">${safeName}</span>
            <input type="hidden" name="products[${id}][id_produk]" value="${id}">
            <input type="hidden" name="products[${id}][harga]" value="${price}" class="item-price">
            <input type="hidden" name="products[${id}][diskon]" value="${discount}" class="item-discount">
            <input type="hidden" name="products[${id}][harga_jual]" value="${subtotal}" class="item-harga-jual">
        </td>
        <td class="text-end">Rp ${formatUang(price)}</td>
        <td class="text-end">${discount > 0 ? 'Rp ' + formatUang(discount) : '-'}</td>
        <td class="text-center">
            <div class="input-group input-group-sm" style="max-width: 110px; margin: 0 auto;">
                <button type="button" class="btn btn-outline-secondary" onclick="ubahKuantitasItem(${id}, -1)">-</button>
                <input type="number" name="products[${id}][kuantitas]" value="${qty}" min="1" class="form-control text-center item-qty" onchange="validasiKuantitas(${id})" oninput="updateKuantitasBaris(${id})">
                <button type="button" class="btn btn-outline-secondary" onclick="ubahKuantitasItem(${id}, 1)">+</button>
            </div>
        </td>
        <td class="text-end fw-bold item-subtotal">Rp ${formatUang(subtotal)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="hapusBarisKeranjang(${id})" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    hitungKalkulasiPOS();
}

function ubahKuantitasItem(id, delta) {
    const row = document.getElementById('cart-row-' + id);
    if (!row) return;
    const qtyInput = row.querySelector('.item-qty');
    let qty = parseInt(qtyInput.value) || 1;
    qty += delta;
    if (qty < 1) qty = 1;
    qtyInput.value = qty;

    updateSubtotalBaris(row);
    hitungKalkulasiPOS();
}

function validasiKuantitas(id) {
    const row = document.getElementById('cart-row-' + id);
    if (!row) return;
    const qtyInput = row.querySelector('.item-qty');
    let qty = parseInt(qtyInput.value) || 1;
    if (qty < 1) qty = 1;
    qtyInput.value = qty;

    updateSubtotalBaris(row);
    hitungKalkulasiPOS();
}

function updateKuantitasBaris(id) {
    const row = document.getElementById('cart-row-' + id);
    if (!row) return;
    updateSubtotalBaris(row);
    hitungKalkulasiPOS();
}

function updateSubtotalBaris(row) {
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
    const qty = Math.max(1, parseInt(row.querySelector('.item-qty').value) || 1);

    const subtotal = Math.max(0, price - discount) * qty;

    const hargaJualInput = row.querySelector('.item-harga-jual');
    if (hargaJualInput) {
        hargaJualInput.value = subtotal;
    }

    const subtotalDisplay = row.querySelector('.item-subtotal');
    if (subtotalDisplay) {
        subtotalDisplay.innerText = 'Rp ' + formatUang(subtotal);
    }
}

function hapusBarisKeranjang(id) {
    const row = document.getElementById('cart-row-' + id);
    if (row) {
        row.remove();
    }
    const tbody = document.getElementById('cart-table-body');
    const remaining = tbody.querySelectorAll('tr.cart-row');
    if (remaining.length === 0) {
        const emptyRow = document.getElementById('empty-cart-row');
        if (emptyRow) emptyRow.style.display = '';
    }
    hitungKalkulasiPOS();
}

function hitungKalkulasiPOS() {
    const tbody = document.getElementById('cart-table-body');
    if (!tbody) return;

    let totalProduk = 0;
    let totalQty = 0;

    tbody.querySelectorAll('tr.cart-row').forEach(function (row) {
        const hargaJualInput = row.querySelector('.item-harga-jual');
        const qtyInput = row.querySelector('.item-qty');
        totalProduk += parseFloat(hargaJualInput ? hargaJualInput.value : 0) || 0;
        totalQty += parseInt(qtyInput ? qtyInput.value : 0) || 0;
    });

    let donasiVal = 0;
    const donationSelect = document.getElementById('donation-select');
    if (donationSelect && donationSelect.value) {
        const selectedOpt = donationSelect.options[donationSelect.selectedIndex];
        donasiVal = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
    }

    const grandTotal = totalProduk + donasiVal;

    const subtotalDisplay = document.getElementById('subtotal-display');
    if (subtotalDisplay) subtotalDisplay.innerText = 'Rp ' + formatUang(totalProduk);

    const donationDisplay = document.getElementById('donation-display');
    if (donationDisplay) donationDisplay.innerText = 'Rp ' + formatUang(donasiVal);

    const totalDisplay = document.getElementById('total-display');
    if (totalDisplay) totalDisplay.innerText = 'Rp ' + formatUang(grandTotal);

    const hiddenTotal = document.getElementById('hidden-total');
    if (hiddenTotal) hiddenTotal.value = grandTotal;

    const itemCountBadge = document.getElementById('item-count-badge');
    if (itemCountBadge) itemCountBadge.innerText = totalQty + ' item';

    const cashInput = document.getElementById('cash-input');
    const qrisInput = document.getElementById('qris-input');
    const cash = parseFloat(cashInput ? cashInput.value : 0) || 0;
    const qris = parseFloat(qrisInput ? qrisInput.value : 0) || 0;
    const totalBayar = cash + qris;
    const change = totalBayar - grandTotal;

    const changeDisplay = document.getElementById('change-display');
    if (changeDisplay) {
        if (totalBayar === 0 && grandTotal === 0) {
            changeDisplay.innerText = 'Rp 0';
            changeDisplay.className = 'fw-bold text-muted';
        } else if (change >= 0) {
            changeDisplay.innerText = 'Rp ' + formatUang(change);
            changeDisplay.className = 'fw-bold text-success';
        } else {
            changeDisplay.innerText = '- Rp ' + formatUang(Math.abs(change)) + ' (Kurang)';
            changeDisplay.className = 'fw-bold text-danger';
        }
    }

    const hiddenChange = document.getElementById('hidden-change');
    if (hiddenChange) {
        hiddenChange.value = Math.max(0, change);
    }
}

function formatUang(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
