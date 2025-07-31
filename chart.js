// chart.js
// Renders the forecast comparison chart using Chart.js.

document.addEventListener('DOMContentLoaded', function() {
    console.log("Chart.js script loaded. Chart data available:", chartData);

    // Get the canvas element
    var ctx = document.getElementById('forecastChart');

    // Check if the canvas element exists
    if (!ctx) {
        console.error("Canvas element with ID 'forecastChart' not found.");
        return;
    }

    // Check if chartData is available from the PHP script
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        console.warn("No chart data available from PHP. Chart will not be rendered.");
        // Optionally, display a message on the canvas or hide the chart container
        ctx.parentNode.innerHTML = '<p>No data available to display chart.</p>';
        return;
    }

    // --- Create the Chart ---
    var forecastChart = new Chart(ctx, {
        type: 'bar', // Use a bar chart
        data: {
            labels: chartData.labels, // Product Names
            datasets: [
                {
                    label: 'Linear Regression Prediction',
                    data: chartData.lr_data, // Predicted demands from LR
                    backgroundColor: 'rgba(0, 123, 255, 0.6)', // Blue with some transparency
                    borderColor: 'rgba(0, 123, 255, 1)', // Darker blue border
                    borderWidth: 1
                },
                {
                    label: 'Random Forest Prediction',
                    data: chartData.rf_data, // Predicted demands from RF
                    backgroundColor: 'rgba(40, 167, 69, 0.6)', // Green with some transparency
                    borderColor: 'rgba(40, 167, 69, 1)', // Darker green border
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allow it to fill the container height
            plugins: {
                title: {
                    display: true,
                    text: 'Demand Forecast Comparison (Next Month)',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'top', // Position of the legend
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true, // Y-axis starts at 0
                    title: {
                        display: true,
                        text: 'Predicted Demand'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Product'
                    }
                }
            }
        }
    });

    console.log("Forecast chart rendered successfully.");
});