/**
 * Chart.js for Weekly/Monthly Reports page
 */
(function() {
    const data = window.reportsStudentData;
    if (!data || !data.labels) return;

    const moodLabels = { 1: '😞 Sad', 2: '😐 Neutral', 3: '😊 Happy' };

    const ctxSleep = document.getElementById('chartReportSleep');
    if (ctxSleep && data.sleep.length) {
        new Chart(ctxSleep, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Sleep (hrs)',
                    data: data.sleep,
                    backgroundColor: '#4a90e2',
                    borderColor: '#357abd',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: { beginAtZero: true, max: 12, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    const ctxStudy = document.getElementById('chartReportStudy');
    if (ctxStudy && data.study.length) {
        new Chart(ctxStudy, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Study (hrs)',
                    data: data.study,
                    backgroundColor: '#4caf50',
                    borderColor: '#388e3c',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    const ctxMood = document.getElementById('chartReportMood');
    if (ctxMood && data.mood.length) {
        new Chart(ctxMood, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Mood',
                    data: data.mood,
                    borderColor: '#ff9800',
                    backgroundColor: 'rgba(255,152,0,0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        min: 0.5,
                        max: 3.5,
                        ticks: { stepSize: 1, callback: function(v) { return moodLabels[v] || v; } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return moodLabels[ctx.raw] || ctx.raw; }
                        }
                    }
                }
            }
        });
    }
})();
