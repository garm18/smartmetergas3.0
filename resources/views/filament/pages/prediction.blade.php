<x-filament::page>
    <div class="filament-card bg-white shadow-lg rounded-xl p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Prediksi Data Penggunaan</h2>
        <canvas id="predictionChart" class="w-full h-96"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const apiURL = "https://iqsansyachranie.pythonanywhere.com/predict";

            try {
                const response = await fetch(apiURL);
                const data = await response.json();

                const dates = Object.keys(data.predictions);
                const predictions = Object.values(data.predictions);

                const ctx = document.getElementById('predictionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Prediksi',
                            data: predictions,
                            borderColor: '#4F46E5',
                            backgroundColor: 'rgba(79, 70, 229, 0.2)',
                            borderWidth: 3,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#4F46E5'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Prediksi Data berdasarkan API',
                                font: {
                                    size: 18,
                                    weight: 'bold'
                                },
                                color: '#374151'
                            },
                            legend: {
                                labels: {
                                    color: '#374151',
                                    font: {
                                        size: 14
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Tanggal',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    color: '#6B7280'
                                },
                                ticks: {
                                    color: '#6B7280'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Nilai Prediksi',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    color: '#6B7280'
                                },
                                ticks: {
                                    color: '#6B7280'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        });
    </script>
</x-filament::page>
