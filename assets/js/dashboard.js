document.addEventListener('DOMContentLoaded', function () {

    if (typeof wfgStats === 'undefined') {
        return;
    }

    const ctx = document.getElementById('wfgChart');

    if (!ctx) {
        return;
    }

    const labels = Array.isArray(wfgStats.labels) ? wfgStats.labels : [];
    const blocked = Array.isArray(wfgStats.blocked) ? wfgStats.blocked : [];
    const passed = Array.isArray(wfgStats.passed) ? wfgStats.passed : [];

    if (!labels.length) {
        return;
    }

    const width = 700;
    const height = 260;
    const padding = 30;

    ctx.width = width;
    ctx.height = height;

    const chart = ctx.getContext('2d');
    chart.clearRect(0, 0, width, height);

    const maxValue = Math.max(...blocked, ...passed, 1);
    const stepX = (width - padding * 2) / Math.max(labels.length - 1, 1);
    const stepY = (height - padding * 2) / Math.max(maxValue, 1);

    chart.strokeStyle = '#d0d7de';
    chart.lineWidth = 1;

    for (let i = 0; i <= 4; i++) {
        const y = padding + ((height - padding * 2) / 4) * i;
        chart.beginPath();
        chart.moveTo(padding, y);
        chart.lineTo(width - padding, y);
        chart.stroke();
    }

    const drawSeries = function (values, color) {
        chart.beginPath();
        values.forEach(function (value, index) {
            const x = padding + stepX * index;
            const y = height - padding - value * stepY;

            if (index === 0) {
                chart.moveTo(x, y);
            } else {
                chart.lineTo(x, y);
            }
        });
        chart.strokeStyle = color;
        chart.lineWidth = 2;
        chart.stroke();
    };

    drawSeries(blocked, '#d63638');
    drawSeries(passed, '#0a7b3e');

    chart.fillStyle = '#1d2327';
    chart.font = '12px sans-serif';
    labels.forEach(function (label, index) {
        const x = padding + stepX * index;
        chart.fillText(label, x - 12, height - 8);
    });
});