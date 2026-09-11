// ===== Hamburger menu (JS-driven, menggantikan checkbox hack) =====
function initNavToggle() {
    const toggleBtn = document.getElementById("nav-toggle-btn");
    const nav = document.querySelector("header nav");
    if (!toggleBtn || !nav) return;

    toggleBtn.addEventListener("click", function () {
        const isOpen = nav.classList.toggle("nav-open");
        toggleBtn.setAttribute("aria-expanded", isOpen);
    });
}

// ===== Counter jumlah baris tersisa (Latihan 8.4 No. 4) =====
function updateRowCounter(table) {
    if (!table) return;
    const searchBox = document.querySelector(".search-box") || table.closest(".table-responsive");
    let counter = document.getElementById("row-counter");
    if (!counter && searchBox) {
        counter = document.createElement("p");
        counter.id = "row-counter";
        counter.className = "row-counter";
        searchBox.insertAdjacentElement("afterend", counter);
    }
    if (!counter) return;

    const rows = table.querySelectorAll("tbody tr");
    const total = rows.length;
    const visible = Array.from(rows).filter(function (r) {
        return r.style.display !== "none";
    }).length;

    const heading = document.querySelector("main h2")?.textContent.toLowerCase() || "";
    const entity = heading.includes("buku") ? "buku" : (heading.includes("anggota") ? "anggota" : "data");

    counter.textContent = "Menampilkan " + visible + " dari " + total + " " + entity;
}

// ===== Konfirmasi hapus (front-end only, event delegation untuk Jobsheet 6) =====
// Memakai event delegation di document karena baris tabel sekarang
// dirender dinamis via fetch (lihat buku.js/anggota.js) sehingga
// tombol .btn-hapus belum tentu ada saat DOMContentLoaded.
function initHapusConfirm() {
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-hapus");
        if (!btn) return;

        const row = btn.closest("tr");
        const table = row ? row.closest("table") : null;
        const nama = row ? row.querySelector("td")?.textContent : "data ini";
        const yakin = confirm("Yakin ingin menghapus \"" + nama + "\"?");
        if (yakin && row) {
            row.remove();
            if (table) updateRowCounter(table);
        }
    });
}

// ===== Filter/pencarian tabel real-time (dibatasi pada kolom tertentu) =====
function initTableFilter() {
    const input = document.getElementById("search-input");
    const table = document.querySelector(".table-responsive table");
    if (!table) return;

    // Tampilkan counter saat halaman pertama kali dimuat
    updateRowCounter(table);

    if (!input) return;

    // Cari kolom target pencarian: kolom "Judul" pada buku, atau "Nama" pada anggota
    const headers = Array.from(table.querySelectorAll("thead th"));
    let targetIndex = headers.findIndex(function (th) {
        const text = th.textContent.toLowerCase();
        return text.includes("judul") || text.includes("nama");
    });
    if (targetIndex === -1) targetIndex = 0;

    input.addEventListener("keyup", function () {
        const keyword = input.value.toLowerCase();
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(function (row) {
            // Ambil sel pada kolom target (menggunakan pola querySelector('td') seperti di bab 5 §5.4)
            const cells = row.querySelectorAll("td");
            const targetCell = cells[targetIndex] || row.querySelector("td");
            const teks = targetCell ? targetCell.textContent.toLowerCase() : "";
            row.style.display = teks.includes(keyword) ? "" : "none";
        });
        updateRowCounter(table);
    });
}

// ===== Validasi form (client-side) =====
function tampilkanError(input, pesan) {
    hapusError(input);
    input.classList.add("invalid");
    const span = document.createElement("span");
    span.className = "error";
    span.textContent = pesan;
    input.insertAdjacentElement("afterend", span);
}

function hapusError(input) {
    input.classList.remove("invalid");
    const next = input.nextElementSibling;
    if (next && (next.classList.contains("error") || next.classList.contains("error-msg"))) {
        next.remove();
    }
    const errorIsbn = document.getElementById("error-isbn");
    if (errorIsbn && input.id === "isbn") {
        errorIsbn.textContent = "";
    }
}

// ===== Refactor Validasi Form (Latihan 8.4 No. 5) =====
function initValidasiForm() {
    const form = document.getElementById("form-tambah");
    if (!form) return;

    // Daftar aturan validasi field berbasis array
    const aturanValidasi = [
        {
            selector: "[name='judul'], [name='nama']",
            pesan: "Field ini wajib diisi.",
            cek: function (input) {
                return input.value.trim() !== "";
            }
        },
        {
            selector: "[name='pengarang']",
            pesan: "Pengarang wajib diisi.",
            cek: function (input) {
                return input.value.trim() !== "";
            }
        },
        {
            selector: "[name='no_anggota']",
            pesan: "No. Anggota wajib diisi.",
            cek: function (input) {
                return input.value.trim() !== "";
            }
        },
        {
            selector: "[name='tahun']",
            pesan: "Tahun harus di antara 1900-2026.",
            cek: function (input) {
                const nilai = parseInt(input.value, 10);
                return !isNaN(nilai) && nilai >= 1900 && nilai <= 2026;
            }
        },
        {
            selector: "[name='stok']",
            pesan: "Stok tidak boleh negatif.",
            cek: function (input) {
                const nilai = parseInt(input.value, 10);
                return !isNaN(nilai) && nilai >= 0;
            }
        },
        {
            selector: "[name='isbn']",
            pesan: "ISBN hanya boleh berisi angka dan tanda hubung (-).",
            cek: function (input) {
                const val = input.value.trim();
                return val === "" || /^[0-9-]+$/.test(val);
            }
        }
    ];

    form.addEventListener("submit", function (e) {
        let valid = true;

        // Validasi tiap field menggunakan loop forEach pada array aturan
        aturanValidasi.forEach(function (aturan) {
            const input = form.querySelector(aturan.selector);
            if (!input) return; // Guard clause jika elemen tidak ada di halaman ini

            if (!aturan.cek(input)) {
                tampilkanError(input, aturan.pesan);
                valid = false;
            } else {
                hapusError(input);
            }
        });

        if (!valid) {
            e.preventDefault();
        }
    });

    // Hapus pesan error secara real-time saat pengguna memperbaiki input
    aturanValidasi.forEach(function (aturan) {
        const input = form.querySelector(aturan.selector);
        if (!input) return;

        input.addEventListener("input", function () {
            if (aturan.cek(input)) {
                hapusError(input);
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initHapusConfirm();
    initTableFilter();
    initValidasiForm();
});
