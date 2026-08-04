<style>
/* Report Shared Styles */
.tracking-wider { letter-spacing: 0.05em; }

.hover-lift { 
    transition: transform 0.2s ease, box-shadow 0.2s ease; 
}
.hover-lift:hover { 
    transform: translateY(-3px); 
    box-shadow: var(--ims-shadow-md, 0 4px 6px -1px rgba(24,34,48,.07), 0 2px 4px -2px rgba(24,34,48,.05)) !important; 
}

/* Unified Table */
.custom-table {
    margin-bottom: 0;
}
.custom-table th { 
    background-color: var(--ims-surface, #fff); 
    border-bottom: 1px solid var(--ims-border, #e2e8f0); 
    color: var(--ims-muted, #64748b);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.02em;
    padding-top: 1rem;
    padding-bottom: 1rem;
}
.custom-table td { 
    border-bottom: 1px solid var(--ims-border-light, #eef2f8); 
    color: var(--ims-ink, #182230); 
    vertical-align: middle;
}
.custom-table tbody tr:hover td {
    background-color: rgba(5, 77, 158, 0.025);
}

/* Stat Cards */
.stat-card {
    border-radius: var(--ims-radius-lg, 16px);
    border: 1px solid var(--ims-border, #e2e8f0);
    box-shadow: var(--ims-shadow, 0 1px 3px rgba(24,34,48,.06), 0 1px 2px rgba(24,34,48,.04));
    background: white;
    height: 100%;
    position: relative;
    overflow: hidden;
}
.stat-card .icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}
.stat-card .value {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--ims-ink, #182230);
    line-height: 1.2;
    margin-bottom: 0.25rem;
}
.stat-card .label {
    font-size: 11px;
    font-weight: 500;
    color: var(--ims-muted, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.stat-card .trend-indicator {
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 0.5rem;
}

/* Chart Cards */
.chart-card {
    border: 0;
    border-radius: var(--ims-radius-lg, 16px);
    box-shadow: var(--ims-shadow-sm, 0 1px 2px rgba(0,0,0,0.05));
    height: 100%;
}
.chart-card-header {
    background: transparent;
    border-bottom: 0;
    padding: 1.5rem 1.5rem 0 1.5rem;
}
.chart-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ims-ink, #182230);
    margin: 0;
}
.chart-card-body {
    padding: 1.5rem;
    position: relative;
}

/* KPI Rings (Trainee View) */
.kpi-ring {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1rem auto;
    position: relative;
}
.kpi-ring::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    padding: 3px;
    background: conic-gradient(currentColor var(--progress, 0%), transparent 0);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.3;
}

/* Print Styles */
@media print {
    body {
        background: white;
    }
    .sidenav, .topbar, .site-footer, .filter-bar, .btn {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
}
</style>
