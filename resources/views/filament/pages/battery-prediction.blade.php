<x-filament::page>
    <div class="p-2 space-y-4">
        <!-- Error Message Container -->
        <div id="error-message" class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl hidden">
            Error loading data. Please try again later.
        </div>

        <!-- Chart Container -->
        <div class="relative bg-white rounded-xl shadow-sm border border-gray-200">
            <div id="loading" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center z-10">
                <div class="flex items-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Loading data...</span>
                </div>
            </div>
            <div class="p-4 h-96">
                <canvas id="batteryChart"></canvas>
            </div>
        </div>

        <!-- Statistics Panel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Prediction Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-gray-500">Starting Battery</div>
                    <div id="startBattery" class="text-lg font-semibold text-gray-900">Loading...</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-gray-500">Current Battery</div>
                    <div id="currentBattery" class="text-lg font-semibold text-gray-900">Loading...</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-gray-500">Battery Drain Rate</div>
                    <div id="drainRate" class="text-lg font-semibold text-gray-900">Loading...</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-gray-500">Estimated Time to 0%</div>
                    <div id="timeToZero" class="text-lg font-semibold text-gray-900">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Refresh Button -->
        <div class="flex justify-center">
            <button id="refreshButton" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                <span>Refresh Data</span>
            </button>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Variabel untuk menyimpan instance chart
        let batteryChart = null;
        
        // Fungsi untuk memformat waktu
        function formatTime(hours) {
            const days = Math.floor(hours / 24);
            const remainingHours = Math.floor(hours % 24);
            return days > 0 ? `${days}d ${remainingHours}h` : `${remainingHours}h`;
        }
        
        // Fungsi untuk memuat data dari API
        async function loadData() {
            try {
                document.getElementById('loading').style.display = 'flex';
                document.getElementById('error-message').style.display = 'none';
                
                const response = await fetch('https://dhiasuhaila.pythonanywhere.com/predict');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                processData(data);
            } catch (error) {
                console.error('Error fetching data:', error);
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-message').textContent = `Error loading data: ${error.message}`;
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }
        
        // Fungsi untuk memproses data dan membuat chart
        function processData(data) {
            // Menghitung statistik
            const startBattery = data.predictions[0];
            const currentBattery = data.predictions[data.predictions.length - 1];
            const totalHours = data.hours_since_start[data.hours_since_start.length - 1];
            const totalDrain = startBattery - currentBattery;
            const drainRate = totalDrain / totalHours;
            
            // Update statistik di halaman
            document.getElementById('startBattery').textContent = startBattery.toFixed(2) + '%';
            document.getElementById('currentBattery').textContent = currentBattery.toFixed(2) + '%';
            document.getElementById('drainRate').textContent = drainRate.toFixed(4) + '% per hour';
            
            // Format waktu ke 0% menjadi hari dan jam
            const timeToZero = data.t_zero_hours;
            const days = Math.floor(timeToZero / 24);
            const hours = Math.floor(timeToZero % 24);
            document.getElementById('timeToZero').textContent = `${days} hari, ${hours} jam (${timeToZero.toFixed(1)} jam total)`;
            
            // Membuat dataset untuk chart
            // Pilih subset titik data untuk membuat chart lebih bersih
            const skipFactor = Math.max(1, Math.floor(data.hours_since_start.length / 100));
            const filteredIndices = data.hours_since_start.map((_, i) => i).filter(i => i % skipFactor === 0 || i === data.hours_since_start.length - 1);
            
            const filteredHours = filteredIndices.map(i => data.hours_since_start[i]);
            const filteredPredictions = filteredIndices.map(i => data.predictions[i]);
            
            // Konversi jam ke format waktu yang lebih mudah dibaca
            const formattedLabels = filteredHours.map(formatTime);

            // Hancurkan chart lama jika ada
            if (batteryChart) {
                batteryChart.destroy();
            }

            // Membuat chart dengan Chart.js
            const ctx = document.getElementById('batteryChart').getContext('2d');
            batteryChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: formattedLabels,
                    datasets: [{
                        label: 'Battery Percentage',
                        data: filteredPredictions,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Time Since Start (days and hours)'
                            }
                        },
                        y: {
                            beginAtZero: false,
                            min: Math.floor(Math.min(...data.predictions) - 5),
                            max: 100,
                            title: {
                                display: true,
                                text: 'Battery Percentage (%)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Battery Level Prediction Over Time',
                            font: {
                                size: 18
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const hours = filteredHours[index];
                                    const days = Math.floor(hours / 24);
                                    const remainingHours = Math.floor(hours % 24);
                                    const timeStr = days > 0 ? `${days} days, ${remainingHours} hours` : `${hours.toFixed(1)} hours`;
                                    return `Battery: ${context.raw.toFixed(2)}% (Time: ${timeStr})`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Panggil fungsi untuk memuat data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', loadData);
        
        // Event listener untuk tombol refresh
        document.getElementById('refreshButton').addEventListener('click', loadData);
    </script>
    @endpush
</x-filament::page>