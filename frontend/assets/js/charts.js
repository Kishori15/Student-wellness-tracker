/**
 * Chart.js integration for Student Wellness Dashboard
 * Renders charts based on data passed from PHP via window objects
 */

document.addEventListener('DOMContentLoaded', function() {
    // Student Dashboard Charts
    if (window.studentDashboardData) {
        const data = window.studentDashboardData;

        // Bar Chart: Study Hours This Week
        const ctxStudy = document.getElementById('chartStudyHours');
        if (ctxStudy) {
            new Chart(ctxStudy, {
                type: 'bar',
                data: {
                    labels: data.days,
                    datasets: [{
                        label: 'Study Hours',
                        data: data.studyHours,
                        backgroundColor: '#4a90e2',
                        borderColor: '#357abd',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 15,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Line Chart: Sleep Trend This Week
        const ctxSleep = document.getElementById('chartSleepTrend');
        if (ctxSleep) {
            new Chart(ctxSleep, {
                type: 'line',
                data: {
                    labels: data.days,
                    datasets: [{
                        label: 'Sleep Hours',
                        data: data.sleepHours,
                        borderColor: '#4a90e2',
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#4a90e2'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            ticks: {
                                stepSize: 2
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Pie Chart: Stress Level Distribution
        const ctxStressPie = document.getElementById('chartStressPie');
        if (ctxStressPie && data.stressDistribution) {
            const total = data.stressDistribution.low + data.stressDistribution.moderate + data.stressDistribution.high;
            if (total > 0) {
                new Chart(ctxStressPie, {
                    type: 'pie',
                    data: {
                        labels: ['Low', 'Moderate', 'High'],
                        datasets: [{
                            data: [
                                data.stressDistribution.low,
                                data.stressDistribution.moderate,
                                data.stressDistribution.high
                            ],
                            backgroundColor: [
                                '#4caf50',
                                '#ff9800',
                                '#f44336'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15
                                }
                            }
                        }
                    }
                });
            }
        }

        // Gauge/Donut Chart: Wellness Score (half-donut style)
        const ctxGauge = document.getElementById('chartWellnessGauge');
        if (ctxGauge) {
            // Calculate wellness score (rule-based: average of normalized metrics)
            const avgSleep = data.sleepHours.reduce((a, b) => a + b, 0) / data.sleepHours.length || 0;
            const normalizedSleep = Math.min(avgSleep / 8, 1) * 100; // 8 hrs = 100%
            const wellnessScore = Math.round(normalizedSleep); // Simplified score

            new Chart(ctxGauge, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [wellnessScore, 100 - wellnessScore],
                        backgroundColor: [
                            wellnessScore >= 70 ? '#4caf50' : wellnessScore >= 40 ? '#ff9800' : '#f44336',
                            '#e0e0e0'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '70%',
                    rotation: -90,
                    circumference: 180,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    }
                },
                plugins: [{
                    id: 'gaugeLabel',
                    afterDraw: (chart) => {
                        const ctx = chart.ctx;
                        const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                        const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2 + 20;
                        ctx.save();
                        ctx.font = 'bold 24px sans-serif';
                        ctx.fillStyle = '#333';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(wellnessScore + '%', centerX, centerY);
                        ctx.restore();
                    }
                }]
            });
        }
    }

    // Admin Dashboard Charts
    if (window.adminDashboardData) {
        const data = window.adminDashboardData;

        // Bar Chart: Monthly Sleep Average
        const ctxAdminBar = document.getElementById('chartAdminBar');
        if (ctxAdminBar) {
            new Chart(ctxAdminBar, {
                type: 'bar',
                data: {
                    labels: data.months,
                    datasets: [{
                        label: 'Sleep Hours',
                        data: data.sleepData,
                        backgroundColor: '#4a90e2',
                        borderColor: '#357abd',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Line Chart: Monthly Study Average
        const ctxAdminLine = document.getElementById('chartAdminLine');
        if (ctxAdminLine) {
            new Chart(ctxAdminLine, {
                type: 'line',
                data: {
                    labels: data.months,
                    datasets: [{
                        label: 'Study Hours',
                        data: data.studyData,
                        borderColor: '#4caf50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#4caf50'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    }
});
