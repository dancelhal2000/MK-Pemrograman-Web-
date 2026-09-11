// assets/js/buku.js
// Catatan: Latihan 8.4 No. 2 & No. 3 - Kolom "kategori" ditambahkan ke tabel dan data buku.

async function muatDaftarBuku() {
    if (typeof muatDataTabel === "function") {
        return muatDataTabel("../data/buku.json", ["judul", "pengarang", "kategori", "tahun", "stok"], {
            includeDetail: true
        });
    }

    // Implementasi langsung jika buku.js dijalankan secara mandiri (Latihan 8.4 No. 3)
    const tbody = document.querySelector(".table-responsive table tbody");
    const loading = document.getElementById("loading-indicator");
    if (!tbody) return;

    if (loading) loading.style.display = "block";
    tbody.innerHTML = "";

    try {
        await new Promise((resolve) => setTimeout(resolve, 600));

        const res = await fetch("../data/buku.json");
        if (!res.ok) {
            throw new Error("Gagal mengambil data (status " + res.status + ")");
        }
        const daftarBuku = await res.json();

        daftarBuku.forEach(function (buku) {
            const tr = document.createElement("tr");
            tr.innerHTML =
                "<td>" + buku.judul + "</td>" +
                "<td>" + buku.pengarang + "</td>" +
                "<td>" + (buku.kategori || "-") + "</td>" +
                "<td>" + buku.tahun + "</td>" +
                "<td>" + buku.stok + "</td>" +
                "<td>" +
                "<button type=\"button\">Edit</button> " +
                "<button type=\"button\" class=\"detail\">Detail</button> " +
                "<button type=\"button\" class=\"btn-hapus\">Hapus</button>" +
                "</td>";
            tbody.appendChild(tr);
        });

        const table = tbody.closest("table");
        if (table && typeof updateRowCounter === "function") {
            updateRowCounter(table);
        }
    } catch (err) {
        tbody.innerHTML =
            "<tr><td colspan=\"6\">Gagal memuat data: " + err.message + "</td></tr>";
    } finally {
        if (loading) loading.style.display = "none";
    }
}

function initReloadBtn() {
    const btnReload = document.getElementById("btn-reload");
    if (!btnReload) return;

    btnReload.addEventListener("click", function () {
        const searchInput = document.getElementById("search-input");
        if (searchInput) searchInput.value = "";
        muatDaftarBuku();
    });
}

// Hanya inisialisasi jika belum ditangani oleh tabel.js
if (typeof muatDataTabel !== "function") {
    document.addEventListener("DOMContentLoaded", function () {
        muatDaftarBuku();
        initReloadBtn();
    });
}
