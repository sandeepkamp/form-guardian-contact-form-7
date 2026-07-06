document.addEventListener('DOMContentLoaded', function () {

    if (typeof fgucf7Stats === 'undefined') {
        return;
    }

    const ctx = document.getElementById('fgucf7Chart');

    if (!ctx) {
        return;
    }

    const labels = Array.isArray(fgucf7Stats.labels) ? fgucf7Stats.labels : [];
    const blocked = Array.isArray(fgucf7Stats.blocked) ? fgucf7Stats.blocked : [];
    const passed = Array.isArray(fgucf7Stats.passed) ? fgucf7Stats.passed : [];

    if (!labels.length) {
        return;
    }

    const width = ctx.parentElement.offsetWidth;
    const height = 280;
    const padding = 36;

    ctx.width = width * window.devicePixelRatio;
    ctx.height = height * window.devicePixelRatio;
    ctx.style.width = `${width}px`;
    ctx.style.height = `${height}px`;

    const chart = ctx.getContext('2d');
    chart.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
    chart.clearRect(0, 0, width, height);

    const maxValue = Math.max(...blocked, ...passed, 1);
    const stepX = (width - padding * 2) / Math.max(labels.length - 1, 1);
    const stepY = (height - padding * 2) / Math.max(maxValue, 1);

    const gridColor = '#e5e7eb';
    chart.strokeStyle = gridColor;
    chart.lineWidth = 1;
    chart.fillStyle = '#6b7280';
    chart.font = '12px sans-serif';
    chart.textAlign = 'right';
    chart.textBaseline = 'middle';

    for (let i = 0; i <= 4; i++) {
        const y = padding + ((height - padding * 2) / 4) * i;
        const value = Math.round(maxValue - (maxValue / 4) * i);

        chart.beginPath();
        chart.moveTo(padding, y);
        chart.lineTo(width - padding, y);
        chart.stroke();
        chart.fillText(value.toString(), padding - 10, y);
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
        chart.lineWidth = 3;
        chart.stroke();

        values.forEach(function (value, index) {
            const x = padding + stepX * index;
            const y = height - padding - value * stepY;

            chart.beginPath();
            chart.arc(x, y, 4, 0, Math.PI * 2);
            chart.fillStyle = color;
            chart.fill();

            if (value > 0) {
                chart.fillStyle = color;
                chart.font = '11px sans-serif';
                chart.textAlign = 'center';
                chart.textBaseline = 'bottom';
                chart.fillText(value.toString(), x, y - 8);
            }
        });
    };

    const blockedColor = '#d63638';
    const passedColor = '#0a7b3e';

    drawSeries(blocked, blockedColor);
    drawSeries(passed, passedColor);

    chart.fillStyle = '#374151';
    chart.font = '12px sans-serif';
    chart.textAlign = 'center';
    chart.textBaseline = 'top';
    const labelStep = Math.max(1, Math.ceil(labels.length / 7));

    labels.forEach(function (label, index) {
        if (index % labelStep !== 0 && index !== labels.length - 1) {
            return;
        }

        const x = padding + stepX * index;
        const y = height - padding + 10;

        chart.fillText(label, x, y);
    });

    const tooltip = document.querySelector('.fgucf7-chart-tooltip');
    if (!tooltip) {
        return;
    }

    ctx.addEventListener('mousemove', function (event) {
        const rect = ctx.getBoundingClientRect();
        const mouseX = event.clientX - rect.left;
        const closestIndex = Math.round((mouseX - padding) / stepX);
        const index = Math.min(Math.max(closestIndex, 0), labels.length - 1);

        const blockedValue = blocked[index] || 0;
        const passedValue = passed[index] || 0;
        const label = labels[index] || '';

        tooltip.innerHTML = `<strong>${label}</strong><br>${fgucf7Stats.blocked[index] || 0} ${fgucf7Stats.blocked[index] === 1 ? 'blocked' : 'blocked'}<br>${fgucf7Stats.passed[index] || 0} ${fgucf7Stats.passed[index] === 1 ? 'passed' : 'passed'}`;
        tooltip.style.display = 'block';
        tooltip.style.left = `${Math.min(width - 180, Math.max(10, mouseX + 10))}px`;
        tooltip.style.top = '18px';
    });

    ctx.addEventListener('mouseleave', function () {
        tooltip.style.display = 'none';
    });

    window.addEventListener('resize', function () {
        location.reload();
    });
});