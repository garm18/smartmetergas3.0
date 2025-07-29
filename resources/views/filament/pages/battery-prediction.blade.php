<x-filament-panels::page>
    {{-- Tailwind-powered Filament dashboard -------------------------------------------------- --}}

    <div class="mx-auto w-full max-w-6xl space-y-8">
        {{-- METRICS (Grid layout) -------------------------------- --}}
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Card: Hari Tersisa --}}
            <div class="flex flex-col justify-center rounded-lg border border-gray-200 bg-white p-6 text-center shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-center gap-2 text-lg font-medium text-gray-500 dark:text-gray-400">
                    <span class="i-heroicons-clock text-xl"></span>
                    <span>Hari Tersisa (Prediksi)</span>
                </div>
                <div id="daysRemaining" class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-50">-</div>
            </div>

            {{-- Card: Level Baterai --}}
            <div class="flex flex-col justify-center rounded-lg border border-gray-200 bg-white p-6 text-center shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-center gap-2 text-lg font-medium text-gray-500 dark:text-gray-400">
                    <span class="i-heroicons-battery-100 text-xl"></span>
                    <span>Level Baterai (%)</span>
                </div>
                <div id="batteryLevel" class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-50">-</div>
            </div>

            {{-- Card: Umur Baterai --}}
            <div class="flex flex-col justify-center rounded-lg border border-gray-200 bg-white p-6 text-center shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-center gap-2 text-lg font-medium text-gray-500 dark:text-gray-400">
                    <span class="i-heroicons-calendar text-xl"></span>
                    <span>Umur Baterai (Hari)</span>
                </div>
                <div id="batteryAge" class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-50">-</div>
            </div>

            {{-- Card: ID Meter Gas --}}
            <div class="flex flex-col justify-center rounded-lg border border-gray-200 bg-white p-6 text-center shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-center gap-2 text-lg font-medium text-gray-500 dark:text-gray-400">
                    <span class="i-heroicons-identification text-xl"></span>
                    <span>ID Meter Gas</span>
                </div>
                <div id="meterGasId" class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-50">-</div>
            </div>
        </section>

        <br>
        {{-- CHART CARD (Prediksi vs Estimasi) ------------------------------------- --}}
        <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 flex items-center justify-center gap-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                <span class="i-heroicons-chart-line text-xl"></span>
                <span>Prediksi vs Aktual</span>
            </h2>
            <div class="relative h-96 md:h-128 w-full">
                <canvas id="predictionChart"></canvas>
            </div>
        </div>

        <br>
        {{-- REFRESH BUTTON --}}
        <button class="mx-auto block w-fit rounded-full bg-gradient-to-r from-primary-600 to-emerald-500 px-6 py-2 text-sm font-medium text-black shadow transition hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" onclick="loadData()">
            🔄 Refresh Data
        </button>

        {{-- LOADING & ERROR OVERLAYS --}}
        <div id="loading" class="fixed inset-0 z-[60] hidden place-content-center items-center bg-white/80 backdrop-blur-sm dark:bg-gray-900/80">
            <div class="text-center">
                <div class="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-primary-600 dark:border-gray-700 dark:border-t-primary-500"></div>
                <p class="mt-4 text-sm font-medium text-gray-700 dark:text-gray-200">Memuat data prediksi...</p>
            </div>
        </div>
        <div id="error" class="fixed inset-x-4 top-4 z-[70] hidden rounded-xl bg-red-600 p-4 text-sm font-semibold text-white shadow-lg"></div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <script>
            let predictionChart;

            async function loadData() {
                toggleLoading(true);
                try {
                    // CATATAN: ID Meter masih hardcoded (2). Untuk membuatnya dinamis, perlu perubahan lebih lanjut.
                    const response = await fetch('https://suhailadhia.pythonanywhere.com/predict/2');
                    
                    if (!response.ok) {
                        // PERBAIKAN: Gunakan backtick (`) untuk menampilkan pesan error dinamis.
                        throw new Error(`Gagal mengambil data: ${response.status} ${response.statusText}`);
                    }
                    const data = await response.json();
                    
                    // Pastikan respons dari API tidak mengandung error
                    if (data.error) {
                         // PERBAIKAN: Gunakan backtick (`)
                        throw new Error(`API Error: ${data.error}`);
                    }

                    updateUI(data);
                } catch (e) {
                    // PERBAIKAN: Gunakan backtick (`)
                    showError(`Terjadi kesalahan: ${e.message}`);
                    console.error('Error fetching data:', e);
                } finally {
                    toggleLoading(false);
                }
            }

            const toggleLoading = (show) => {
                const loadingEl = document.getElementById('loading');
                if (loadingEl) {
                    loadingEl.classList.toggle('hidden', !show);
                    loadingEl.classList.toggle('flex', show);
                }
            };

            function showError(message) {
                const errorEl = document.getElementById('error');
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.remove('hidden');
                    setTimeout(() => errorEl.classList.add('hidden'), 5000); // Durasi notifikasi lebih lama
                }
            }

            function updateUI(data) {
                // Gunakan nullish coalescing (??) untuk memberikan nilai default jika data tidak ada
                const predictedDays = Math.round(data.predicted_remaining_days ?? 0);
                const batteryLevel = Math.round(data.current_battery_level ?? 0);
                const batteryAge = Math.round(data.current_battery_age_days ?? 0);
                const meterId = data.metergas_id ?? '-';

                document.getElementById('daysRemaining').textContent = predictedDays;
                document.getElementById('batteryLevel').textContent = batteryLevel;
                document.getElementById('batteryAge').textContent = batteryAge;
                document.getElementById('meterGasId').textContent = meterId;
                
                // PERBAIKAN: Menghapus referensi ke 'summaryText' yang tidak ada di HTML
                // document.getElementById('summaryText').innerHTML = getSummaryMessage(predictedDays); 
                
                createOrUpdateChart(data);
            }

            function createOrUpdateChart(apiData) {
                const canvas = document.getElementById('predictionChart');
                if (!canvas) {
                    console.error('Canvas element not found');
                    return;
                }
                const ctx = canvas.getContext('2d');

                if (predictionChart) {
                    predictionChart.destroy();
                }

                // Membuat label untuk 7 hari terakhir
                const labels = Array.from({ length: 7 }, (_, i) => {
                    const date = new Date();
                    date.setDate(date.getDate() - (6 - i));
                    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                });

                // --- PENTING: Data grafik ini adalah SIMULASI / PALSU ---
                // Data ini dibuat secara acak hanya untuk tujuan visualisasi.
                // Untuk data nyata, API Anda harus menyediakan riwayat data historis.
                const latestPrediction = apiData.predicted_remaining_days ?? 0;
                const actualData = Array.from({ length: 7 }, (_, i) => Math.round(latestPrediction + (6 - i) + (Math.random() - 0.5) * 2));
                const predictionData = Array.from({ length: 7 }, (_, i) => Math.round(latestPrediction + (6 - i) + (Math.random() - 0.5) * 1.5));

                predictionChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Prediksi (Simulasi)', // Label diubah untuk kejelasan
                            data: predictionData,
                            borderColor: 'hsl(347, 89%, 61%)',
                            backgroundColor: 'hsla(347, 89%, 61%, 0.2)',
                            fill: true,
                            tension: 0.4,
                        }, {
                            label: 'Aktual (Simulasi)', // Label diubah untuk kejelasan
                            data: actualData,
                            borderColor: 'hsl(159, 83%, 44%)',
                            backgroundColor: 'hsla(159, 83%, 44%, 0.2)',
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                     // PERBAIKAN: Gunakan backtick (`)
                                    label: (context) => `${context.dataset.label}: ${context.formattedValue} hari`,
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Hari Tersisa' } },
                            x: { title: { display: true, text: 'Tanggal' } }
                        }
                    }
                });
            }

            // --- EKSEKUSI SAAT HALAMAN DIBUKA ---
            document.addEventListener('DOMContentLoaded', () => {
                // Inisialisasi grafik kosong agar tidak error saat pertama kali load
                createOrUpdateChart({ predicted_remaining_days: 0 }); 
                
                // Langsung muat data saat halaman dibuka
                loadData(); 

                // Atur interval untuk refresh data setiap 30 detik
                setInterval(loadData, 30000); 
            });
        </script>
    @endpush
</x-filament-panels::page>