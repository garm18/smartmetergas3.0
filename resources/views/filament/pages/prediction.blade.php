<x-filament::page>
    <div class="filament-card">
        <h2 class="text-lg font-bold mb-4">Prediksi Data</h2>
        <canvas id="predictionChart" class="w-full h-96"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const apiURL = "https://iqsansyachranie.pythonanywhere.com/predict";

            try {
                // Fetch data from the API
                const response = await fetch(apiURL);
                const data = await response.json();

                // Extract dates and predictions
                const dates = Object.keys(data.predictions);
                const predictions = Object.values(data.predictions);

                // Initialize Chart.js
                const ctx = document.getElementById('predictionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line', // Chart type
                    data: {
                        labels: dates, // X-axis labels
                        datasets: [{
                            label: 'Prediksi',
                            data: predictions, // Y-axis data
                            borderColor: 'rgba(75, 192, 192, 1)', // Line color
                            backgroundColor: 'rgba(75, 192, 192, 0.2)', // Fill color
                            borderWidth: 2, // Line thickness
                            tension: 0.4 // Smooth curve
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Prediksi Data berdasarkan API'
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Tanggal'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Nilai Prediksi'
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
