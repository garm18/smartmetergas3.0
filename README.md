# SmartMeterGas 3.0

SmartMeterGas 3.0 adalah aplikasi web untuk memantau meter gas. Pelanggan melihat volume gas, baterai, status katup, dan riwayat meter atas nama mereka. Administrator mengelola akun, meter, log, dan data wilayah Indonesia.

## Panel aplikasi

| Panel | Akses | Fitur |
| --- | --- | --- |
| Pelanggan (`/`) | Login dengan email atau username. Akun yang didaftarkan lewat panel ini mendapat peran `customer`. | Ringkasan per meter, grafik volume per jam dan per hari, tabel log, serta status katup. Menu lain menampilkan pemantauan RSSI, prediksi penggunaan, dan prediksi baterai. |
| Admin (`/admin`) | Akun dengan peran `admin` atau `root`. | Kelola pengguna, meter, log, dan wilayah. Pilih baris pada menu **Logs** untuk mengekspornya ke CSV. |

## Sumber data

Dashboard membaca relasi `users` → `metergas` → `logs` dari MySQL. Setiap meter merujuk ke satu pengguna dan dapat dikaitkan dengan provinsi, kabupaten/kota, kecamatan, serta desa/kelurahan. Widget dashboard pelanggan membatasi meter dan log berdasarkan pemilik. Grafik menjumlahkan kolom `volume` per periode, sedangkan ringkasan baterai memakai log terbaru.

| Halaman | Cara mengambil data |
| --- | --- |
| RSSI Monitoring | Server Laravel mengambil riwayat dan hasil prediksi RSSI dari layanan HTTP eksternal. [Grafik RSSI](app/Livewire/RssiChart.php) · [Ringkasan RSSI](app/Livewire/RssiStats.php) |
| Prediction | Browser mengambil prediksi penggunaan lewat `fetch()` dan menampilkannya dengan Chart.js. [Tampilan prediksi](resources/views/filament/pages/prediction.blade.php) |
| Battery Prediction | Browser mengambil prediksi baterai lewat `fetch()` untuk menampilkan tren dan estimasi waktu hingga baterai mencapai 0%. [Tampilan prediksi baterai](resources/views/filament/pages/battery-prediction.blade.php) |

Alamat layanan eksternal tertulis langsung di kode. Halaman RSSI memerlukan akses dari server Laravel. Kedua halaman prediksi memerlukan akses dari browser, termasuk izin CORS untuk permintaan lintas origin. Repositori ini berisi aplikasi web. Penerimaan telemetri dari perangkat dan pemrosesan model prediksi memerlukan komponen di luar repositori.

## Konfigurasi awal

Siapkan database MySQL dan akun khusus aplikasi. Jika belum punya `.env`, salin template dari root proyek.

```bash
cp .env.example .env
```

Isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` sesuai instalasi MySQL. Laravel dan Docker Compose memakai file yang sama.

## Menjalankan dengan Docker

Compose menjalankan satu layanan `app` dengan PHP 8.4 dan Apache. MySQL berjalan di luar Compose. Compose meneruskan konfigurasi dari `.env` lewat `env_file`. Web terikat ke `127.0.0.1` dengan port `8080` secara default. Setel `APP_URL=http://127.0.0.1:8080` di `.env`. Jika mengubah `APP_PORT`, sesuaikan juga port pada `APP_URL`.

```bash
openssl rand -base64 32
```

Jika `APP_KEY` masih kosong, awali hasil OpenSSL dengan `base64:` dan simpan sebagai `APP_KEY` di `.env`. Pertahankan kunci yang sama saat membangun ulang container.

Gunakan `DB_HOST` yang bisa dijangkau dari container. Pada Docker Desktop, gunakan `host.docker.internal` jika MySQL berjalan di host. Di dalam container, `127.0.0.1` mengarah ke container aplikasi. [Akses layanan host dari Docker Desktop](https://docs.docker.com/desktop/features/networking/networking-how-tos/#connect-a-container-to-a-service-on-the-host)

```bash
docker compose build
docker compose run --rm app php artisan migrate --force
docker compose up -d
docker compose ps
```

Jalankan migrasi secara eksplisit pada database yang memang disiapkan untuk aplikasi. Migrasi dari database kosong dan seeder wilayah telah diuji dengan MySQL 8.4. Jika database baru memerlukan pilihan wilayah, jalankan `docker compose run --rm app php artisan db:seed --class=IndoRegionSeeder` sekali. Buka `http://127.0.0.1:8080/register` untuk membuat akun. Gunakan `docker compose exec app php artisan tinker` jika perlu menyiapkan akun admin lokal.

Health check `/up` memeriksa apakah Laravel bisa memulai aplikasi. Gunakan `docker compose exec app php artisan migrate:status` untuk memeriksa koneksi database dan status skemanya.

Kode aplikasi dan aset frontend disalin ke image saat build. Setelah mengubah kode, jalankan `docker compose up -d --build` untuk memakai versi baru.

## Menjalankan tanpa Docker

### Prasyarat

- PHP 8.3 atau 8.4 dan Composer. Dependensi OpenSpout yang terkunci menentukan rentang PHP ini. Aktifkan `pdo_mysql` dan ekstensi yang diminta Composer, termasuk `intl`, `mbstring`, XML, dan `zip`.
- MySQL. Dashboard admin memakai fungsi SQL `MONTH()`, dan konfigurasi aplikasi mendefinisikan koneksi `mysql`.
- Node.js dan npm untuk membangun aset dari `resources/`.

### Instalasi dan data awal

Jika MySQL berjalan di host lokal, isi `DB_HOST=127.0.0.1` untuk koneksi TCP. Isi `DB_SOCKET` dengan path socket jika memakai Unix socket. [Konfigurasi database](config/database.php)

Instal dependensi dari root proyek dengan `composer install`. Jika `APP_KEY` di `.env` masih kosong, jalankan `php artisan key:generate` sebelum melanjutkan.

```bash
php artisan migrate
php artisan db:seed --class=IndoRegionSeeder
php artisan serve
```

Jalankan seeder wilayah sekali pada database baru. Seeder memasukkan provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan untuk pilihan lokasi pada formulir meter. Jika mengubah CSS atau JavaScript di `resources/`, jalankan `npm ci` lalu `npm run build`, atau gunakan `npm run dev` di terminal terpisah selama pengembangan.

### Menyiapkan akun dan meter uji

1. Buka `http://127.0.0.1:8000/register` dan daftarkan akun lokal.
2. Untuk menguji panel admin, jalankan `php artisan tinker` dan ubah peran akun lokal yang baru didaftarkan. Ganti alamat email pada contoh.

   ```php
   App\Models\User::where('email', 'admin@example.test')->firstOrFail()->update(['roles' => 'admin']);
   ```

3. Masuk ke `/admin`. Siapkan satu akun pelanggan, kaitkan sebuah meter dengan akun tersebut di menu **Metergas**, lalu buat log uji lewat menu **Logs**.
4. Masuk ke `/` dengan akun pelanggan untuk melihat meter dan lognya. Halaman RSSI dan prediksi memerlukan layanan eksternal yang dapat dijangkau.

## Peta kode

| Lokasi | Tanggung jawab |
| --- | --- |
| [`app/Providers/Filament/`](app/Providers/Filament/) | Mendaftarkan rute, autentikasi, widget, dan resource untuk kedua panel. |
| [`app/Filament/Admin/`](app/Filament/Admin/) | Formulir dan tabel admin, statistik, serta ekspor log. |
| [`app/Filament/Widgets/`](app/Filament/Widgets/) | Ringkasan meter, grafik volume, tabel log, dan aksi status katup pelanggan. |
| [`app/Models/`](app/Models/) dan [`database/migrations/`](database/migrations/) | Relasi Eloquent dan skema MySQL. |
| [`app/Livewire/`](app/Livewire/) dan [`resources/views/filament/pages/`](resources/views/filament/pages/) | Permintaan data RSSI dan tampilan prediksi. |

## Catatan pemeliharaan

- **Status katup.** Aksi pada aplikasi menyimpan `valve_status` dan mencoba membuat log bertipe `remote`. Kode ini belum menunjukkan pengiriman perintah ke perangkat atau konfirmasi status fisik. Untuk meter tanpa log, aksi pelanggan mencoba menyimpan `volume` dan `battery` sebagai `null`, padahal skema mewajibkan kedua kolom. Karena status tersimpan lebih dulu, kegagalan insert bisa membuat status di dashboard berubah tanpa log baru. [Aksi status katup](app/Filament/Widgets/TableValveStatus.php) · [Skema log](database/migrations/2024_10_19_091951_create_logs_table.php)
- **Agregasi volume.** Grafik menjumlahkan semua log yang cocok. Aksi katup menyalin volume dari log terakhir, jadi perubahan status dapat membuat volume yang sama terhitung lagi. [Grafik volume harian](app/Filament/Widgets/LogDayChart.php)
- **Riwayat RSSI.** Migrasi sinyal menghapus kolom `rssi` lama dan menambahkan `signal_strength` tanpa menyalin riwayatnya. Periksa cadangan data bila memigrasikan database lama yang masih menyimpan kolom itu. [Migrasi sinyal](database/migrations/2025_03_22_155555_update_column_logs.php)
- **Pengujian.** Jalankan `php artisan test` untuk suite Pest yang tersedia. Tes fitur bawaan mengharapkan HTTP 200 dari `/`, sementara dashboard pelanggan meminta login. Suite saat ini berisi tes contoh. [Tes fitur](tests/Feature/ExampleTest.php) · [Konfigurasi panel pelanggan](app/Providers/Filament/AppPanelProvider.php)
