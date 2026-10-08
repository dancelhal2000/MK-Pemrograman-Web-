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

// ===== Counter jumlah baris tersisa =====
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

// ===== Konfirmasi hapus =====
// Tombol Hapus berada di dalam <form class="form-hapus" method="post">
// yang benar-benar mengirim request DELETE ke server (buku/hapus.php, anggota/hapus.php).
// Konfirmasi dilakukan pada event "submit" agar bisa dibatalkan (preventDefault).
function initHapusConfirm() {
    document.addEventListener("submit", function (e) {
        const form = e.target;
        if (!form.classList.contains("form-hapus")) return;

        const row = form.closest("tr");
        const nama = row ? row.querySelector("td")?.textContent : "data ini";
        const yakin = confirm("Yakin ingin menghapus \"" + nama + "\"?");
        if (!yakin) {
            e.preventDefault();
        }
    });
}

// ===== Konfirmasi update (Latihan 7.4 No. 1) =====
// Menambahkan konfirmasi sebelum data di-update ke server (buku/proses_edit.php, anggota/proses_edit.php).
function initUpdateConfirm() {
    document.addEventListener("submit", function (e) {
        if (e.defaultPrevented) return;

        const form = e.target;
        const isEditForm = form.classList.contains("form-edit") || (form.action && form.action.includes("proses_edit.php"));
        if (!isEditForm) return;

        const inputNama = form.querySelector("[name='judul'], [name='nama']");
        const nama = inputNama && inputNama.value.trim() ? inputNama.value.trim() : "data ini";
        const yakin = confirm("Yakin ingin menyimpan perubahan pada \"" + nama + "\"?");
        if (!yakin) {
            e.preventDefault();
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

    // Cari kolom target pencarian: kolom "Judul" & "Pengarang" pada buku, atau "Nama" & "No. Anggota" pada anggota
    const headers = Array.from(table.querySelectorAll("thead th"));
    const targetIndices = [];
    headers.forEach(function (th, idx) {
        const text = th.textContent.toLowerCase();
        if (text.includes("judul") || text.includes("pengarang") || text.includes("nama") || text.includes("anggota")) {
            targetIndices.push(idx);
        }
    });
    if (targetIndices.length === 0) targetIndices.push(0);

    input.addEventListener("keyup", function () {
        const keyword = input.value.toLowerCase();
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(function (row) {
            const cells = row.querySelectorAll("td");
            if (cells.length === 0 || cells[0].hasAttribute("colspan")) return;
            const matches = targetIndices.some(function (idx) {
                const cellText = cells[idx] ? cells[idx].textContent.toLowerCase() : "";
                return cellText.includes(keyword);
            });
            row.style.display = matches ? "" : "none";
        });
        updateRowCounter(table);
    });
}

// ===== Tombol Muat Ulang =====
function initReloadBtn() {
    const btnReload = document.getElementById("btn-reload");
    if (!btnReload) return;

    btnReload.addEventListener("click", function () {
        const searchInput = document.getElementById("search-input");
        if (searchInput) searchInput.value = "";
        if (window.location.search) {
            window.location.href = window.location.pathname;
        } else {
            window.location.reload();
        }
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

// ===== Refactor Validasi Form =====
function initValidasiForm() {
    const forms = document.querySelectorAll("#form-tambah, .form-edit");
    if (forms.length === 0) return;

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

    forms.forEach(function (form) {
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
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initHapusConfirm();
    initUpdateConfirm();
    initTableFilter();
    initReloadBtn();
    initValidasiForm();
});
