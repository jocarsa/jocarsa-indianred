// charts.js

// This file is loaded after the page sets window._chartsData
// and after Chart.js is included.

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    const chartsData = window._chartsData || {};
    const chartsContainer = document.getElementById('charts-container');
    if (!chartsContainer) return; // If no container, do nothing.

    // For each column that has data, create a canvas and build a pie chart
    for (let colName in chartsData) {
        // Prepare a wrapper div
        const chartWrapper = document.createElement('div');
        chartWrapper.classList.add('chart-wrapper');
        chartWrapper.style.display = 'inline-block';
        chartWrapper.style.width = '300px';
        chartWrapper.style.margin = '20px';
        chartWrapper.style.verticalAlign = 'top';

        // Add a heading
        const heading = document.createElement('h3');
        heading.innerText = `Distribución de "${colName}"`;
        chartWrapper.appendChild(heading);

        // Create a <canvas> for Chart.js
        const canvas = document.createElement('canvas');
        canvas.id = 'chart_' + colName;
        canvas.style.width = '300px';
        canvas.style.height = '300px';
        chartWrapper.appendChild(canvas);

        // Append chart wrapper to container
        chartsContainer.appendChild(chartWrapper);

        // Build the pie chart
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartsData[colName].labels,
                datasets: [{
                    label: colName,
                    data: chartsData[colName].data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.4)',
                        'rgba(54, 162, 235, 0.4)',
                        'rgba(255, 206, 86, 0.4)',
                        'rgba(75, 192, 192, 0.4)',
                        'rgba(153, 102, 255, 0.4)',
                        'rgba(255, 159, 64, 0.4)',
                        // add more colors if needed
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        // add more borders if needed
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});

