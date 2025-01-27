<x-filament::layouts.app>
    <div>
        <h1>Prediction Chart</h1>
        <canvas id="myChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        fetch('https://iqsansyachranie.pythonanywhere.com/predict')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('myChart');

                new Chart(ctx, {
                    type: 'bar', // Atau tipe chart lain yang sesuai
                    data: {
                        labels: data.map(item => item.tanggal), // Ambil data tanggal
                        datasets: [{
                            label: 'Jumlah Prediksi', // Label dataset
                            data: data.map(item => item.prediksi), // Ambil data prediksi
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    </script>
</x-filament::layouts.app>