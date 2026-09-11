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

// ===== Konfirmasi hapus (front-end only, belum ke server) =====
function initHapusConfirm() {
    document.querySelectorAll(".btn-hapus").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const row = btn.closest("tr");
            const table = row ? row.closest("table") : null;
            const nama = row ? row.querySelector("td")?.textContent : "data ini";
            const yakin = confirm("Yakin ingin menghapus \"" + nama + "\"?");
            if (yakin && row) {
                row.remove();
                if (table) updateRowCounter(table);
            }
        });
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
    const span = document.createElement("span");
    span.className = "error";
    span.textContent = pesan;
    input.insertAdjacentElement("afterend", span);
}

function hapusError(input) {
    const next = input.nextElementSibling;
    if (next && next.classList.contains("error")) {
        next.remove();
    }
}

// Ambil elemen input dan container error
const isbnInput = document.getElementById('isbn');
const errorIsbn = document.getElementById('error-isbn');

// Regex: hanya mengizinkan angka (0-9) dan tanda hubung (-)
const isbnPattern = /^[0-9-]+$/;

function validateIsbn() {
    const value = isbnInput.value.trim();

    // Karena opsional: jika kosong, dianggap valid
    if (value === '') {
        if (errorIsbn) errorIsbn.textContent = '';
        isbnInput.classList.remove('invalid');
        return true;
    }

    // Jika diisi, periksa apakah sesuai pola angka dan tanda hubung
    if (!isbnPattern.test(value)) {
        if (errorIsbn) {
            errorIsbn.textContent = 'ISBN hanya boleh berisi angka dan tanda hubung (-).';
        }
        isbnInput.classList.add('invalid');
        return false;
    }

    // Valid
    if (errorIsbn) errorIsbn.textContent = '';
    isbnInput.classList.remove('invalid');
    return true;
}

// Integrasi pada event submit form buku
const formBuku = document.querySelector('form');
if (formBuku && isbnInput) {
    formBuku.addEventListener('submit', function (e) {
        const isIsbnValid = validateIsbn();

        if (!isIsbnValid) {
            e.preventDefault(); // Batalkan pengiriman jika tidak valid
        }
    });

    // Validasi realtime saat pengguna mengetik (opsional)
    isbnInput.addEventListener('input', validateIsbn);
}

function initValidasiForm() {
    const form = document.getElementById("form-tambah");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        let valid = true;

        const judul = form.querySelector("[name='judul'], [name='nama']");
        if (judul && judul.value.trim() === "") {
            tampilkanError(judul, "Field ini wajib diisi.");
            valid = false;
        } else if (judul) {
            hapusError(judul);
        }

        const pengarang = form.querySelector("[name='pengarang']");
        if (pengarang && pengarang.value.trim() === "") {
            tampilkanError(pengarang, "Pengarang wajib diisi.");
            valid = false;
        } else if (pengarang) {
            hapusError(pengarang);
        }

        const tahun = form.querySelector("[name='tahun']");
        if (tahun) {
            const nilai = parseInt(tahun.value, 10);
            if (isNaN(nilai) || nilai < 1900 || nilai > 2026) {
                tampilkanError(tahun, "Tahun harus di antara 1900-2026.");
                valid = false;
            } else {
                hapusError(tahun);
            }
        }

        const stok = form.querySelector("[name='stok']");
        if (stok) {
            const nilai = parseInt(stok.value, 10);
            if (isNaN(nilai) || nilai < 0) {
                tampilkanError(stok, "Stok tidak boleh negatif.");
                valid = false;
            } else {
                hapusError(stok);
            }
        }

        if (!valid) {
            e.preventDefault();
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initHapusConfirm();
    initTableFilter();
    initValidasiForm();
});
