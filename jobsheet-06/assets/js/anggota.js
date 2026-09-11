// assets/js/anggota.js
// Catatan: Latihan 8.4 No. 2 - buku.js & anggota.js telah disatukan menjadi fungsi generik muatDataTabel().
// File implementasi utama berada di assets/js/tabel.js.

async function muatDaftarAnggota() {
    if (typeof muatDataTabel === "function") {
        return muatDataTabel("../data/anggota.json", ["no_anggota", "nama", "alamat", "no_hp", "tanggal_bergabung"]);
    }
}

function initReloadBtn() {
    const btnReload = document.getElementById("btn-reload");
    if (!btnReload) return;

    btnReload.addEventListener("click", function () {
        const searchInput = document.getElementById("search-input");
        if (searchInput) searchInput.value = "";
        muatDaftarAnggota();
    });
}

// Hanya inisialisasi jika belum ditangani oleh tabel.js
if (typeof muatDataTabel !== "function") {
    document.addEventListener("DOMContentLoaded", function () {
        muatDaftarAnggota();
        initReloadBtn();
    });
}

