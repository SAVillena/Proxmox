@extends('layouts.app')

@section('content')
    <div>
        <h1>Historico de qemus</h1>
        <p> Ultimo Registro: {{ $VMHistory->last()->total_machines }}</p>
    </div>


    <div>
        <canvas id="qemusChart"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        var ctx = document.getElementById('qemusChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($VMHistory->pluck('date')),
                datasets: [{
                    label: 'Numero de qemus',
                    data: @json($VMHistory->pluck('total_machines')),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                    ],
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
    </script>
    
@endsection
