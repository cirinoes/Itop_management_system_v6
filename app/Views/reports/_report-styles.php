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

/* Stat Cards - Executive Glass Theme */
.stat-card {
    border-radius: var(--ims-radius-lg, 16px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255, 255, 255, 0.8);
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    height: 100%;
    position: relative;
    overflow: hidden;
    padding: 1.25rem !important; /* Force tighter padding */
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-areas: 
        "value icon"
        "label icon"
        "trend trend";
    align-items: start;
    row-gap: 0.25rem;
}
.stat-card .icon-circle {
    grid-area: icon;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 0;
    align-self: center;
    box-shadow: 0 2px 10px -2px rgba(0,0,0,0.1);
}
.stat-card .value {
    grid-area: value;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--ims-ink, #182230);
    line-height: 1;
    margin-bottom: 0;
}
.stat-card .label {
    grid-area: label;
    font-size: 11px;
    font-weight: 500;
    color: var(--ims-muted, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0;
}
.stat-card .trend-indicator {
    grid-area: trend;
    font-size: 0.75rem;
    font-weight: 500;
    margin-top: 0.25rem;
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
    padding: 1.25rem;
}

/* Executive Glass Nav Tabs */
.glass-tabs {
    background: #f1f5f9;
    padding: 0.35rem;
    border-radius: 50px;
    display: inline-flex;
    margin-bottom: 1rem;
}
.glass-tabs .nav-link {
    border: none !important;
    border-radius: 50px !important;
    padding: 0.6rem 1.25rem !important;
    font-weight: 600 !important;
    color: #64748b !important;
    transition: all 0.3s ease;
}
.glass-tabs .nav-link:hover {
    color: #182230 !important;
}
.glass-tabs .nav-link.active {
    background: white !important;
    color: #0d6efd !important;
    box-shadow: 0 2px 8px -2px rgba(0,0,0,0.1) !important;
}

/* Instructor Grid Cards */
.instructor-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: white;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.instructor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
}
.instructor-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
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
