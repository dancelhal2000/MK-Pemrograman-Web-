// assets/js/tabel.js
// Penggabungan buku.js dan anggota.js menjadi satu fungsi generik (Latihan 8.4 No. 2)

/**
 * Fungsi generik untuk mengambil dan menampilkan data JSON ke tabel secara asinkron.
 * Menggantikan duplikasi kode antara buku.js dan anggota.js.
 *
 * @param {string} urlJson - Path ke file JSON (misal: "../data/buku.json" atau "../data/anggota.json")
 * @param {string[]} daftarKunci - Daftar kunci properti objek yang akan dijadikan kolom <td>
 * @param {Object} [options] - Opsi konfigurasi tambahan
 * @param {boolean} [options.includeDetail=false] - Menyertakan tombol Detail pada kolom aksi
 * @param {string} [options.tombolAksi] - Custom HTML untuk kolom aksi jika ada
 */
async function muatDataTabel(urlJson, daftarKunci, options = {}) {
    const tbody = document.querySelector(".table-responsive table tbody");
    const loading = document.getElementById("loading-indicator");
    if (!tbody) return;

    if (loading) loading.style.display = "block";
    tbody.innerHTML = "";

    // Hitung total kolom tabel untuk pesan error colspan dinamis
    const table = tbody.closest("table");
    const totalKolom = (table ? table.querySelectorAll("thead th").length : 0) || (daftarKunci.length + 1);

    try {
        // Simulasi delay jaringan agar loading indicator terlihat
        await new Promise((resolve) => setTimeout(resolve, 600));

        const res = await fetch(urlJson);
        if (!res.ok) {
            throw new Error("Gagal mengambil data (status " + res.status + ")");
        }
        const data = await res.json();

        data.forEach(function (item) {
            const tr = document.createElement("tr");

            // Buat elemen <td> untuk setiap properti dalam daftarKunci
            let tdHtml = "";
            daftarKunci.forEach(function (kunci) {
                const nilai = (item[kunci] !== undefined && item[kunci] !== null && item[kunci] !== "")
                    ? item[kunci]
                    : "-";
                tdHtml += "<td>" + nilai + "</td>";
            });

            // Kolom Aksi
            let aksiHtml = "<td>";
            if (options.tombolAksi) {
                aksiHtml += options.tombolAksi;
            } else if (options.includeDetail) {
                aksiHtml += '<button type="button">Edit</button> ' +
                            '<button type="button" class="detail">Detail</button> ' +
                            '<button type="button" class="btn-hapus">Hapus</button>';
            } else {
                aksiHtml += '<button type="button">Edit</button> ' +
                            '<button type="button" class="btn-hapus">Hapus</button>';
            }
            aksiHtml += "</td>";

            tr.innerHTML = tdHtml + aksiHtml;
            tbody.appendChild(tr);
        });

        // Perbarui counter baris setelah data selesai dirender
        if (table && typeof updateRowCounter === "function") {
            updateRowCounter(table);
        }
    } catch (err) {
        tbody.innerHTML =
            "<tr><td colspan=\"" + totalKolom + "\">Gagal memuat data: " + err.message + "</td></tr>";
    } finally {
        if (loading) loading.style.display = "none";
    }
}

/**
 * Inisialisasi tombol Muat Ulang untuk fungsi reload tertentu
 * @param {Function} reloadCallback - Fungsi yang akan dipanggil saat tombol diklik
 */
function initReloadBtn(reloadCallback) {
    const btnReload = document.getElementById("btn-reload");
    if (!btnReload) return;

    btnReload.onclick = function () {
        const searchInput = document.getElementById("search-input");
        if (searchInput) searchInput.value = "";
        if (typeof reloadCallback === "function") {
            reloadCallback();
        }
    };
}

// Wrapper fungsi untuk data buku
function muatDaftarBuku() {
    return muatDataTabel("../data/buku.json", ["judul", "pengarang", "kategori", "tahun", "stok"], {
        includeDetail: true
    });
}

// Wrapper fungsi untuk data anggota
function muatDaftarAnggota() {
    return muatDataTabel("../data/anggota.json", ["no_anggota", "nama", "alamat", "no_hp", "tanggal_bergabung"]);
}

// Deteksi otomatis halaman dan muat data yang sesuai saat DOM siap
document.addEventListener("DOMContentLoaded", function () {
    const isHalamanBuku = window.location.pathname.includes("/buku/") ||
        Boolean(document.querySelector("input#search-input[placeholder*='judul']")) ||
        Boolean(document.querySelector("thead th") && document.querySelector("thead th").textContent.includes("Judul"));
    const isHalamanAnggota = window.location.pathname.includes("/anggota/") ||
        Boolean(document.querySelector("input#search-input[placeholder*='anggota']")) ||
        Boolean(document.querySelector("thead th") && document.querySelector("thead th").textContent.includes("No. Anggota"));

    if (isHalamanBuku) {
        muatDaftarBuku();
        initReloadBtn(muatDaftarBuku);
    } else if (isHalamanAnggota) {
        muatDaftarAnggota();
        initReloadBtn(muatDaftarAnggota);
    }
});
