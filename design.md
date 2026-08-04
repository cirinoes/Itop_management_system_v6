---
name: ITOP Management System Design
colors:
  primary: '#054d9e'
  primary-dark: '#043972'
  primary-light: '#e8f3ff'
  accent: '#18a999'
  accent-light: '#e6f7f5'
  ink: '#182230'
  soft: '#f4f7fb'
  bg: '#f0f4f8'
  surface: '#ffffff'
  border: '#e2e8f0'
  border-light: '#eef2f8'
  muted: '#64748b'
  danger: '#dc2626'
  danger-light: '#fef2f2'
  success: '#16a34a'
  success-light: '#f0fdf4'
  warning: '#ea580c'
  warning-light: '#fff7ed'
typography:
  fontFamily: Inter
rounded:
  DEFAULT: 12px
  sm: 8px
  lg: 16px
---

# ITOP Management System (IMS)

## Brand & Style
The ITOP Management System is a modern enterprise application. The design relies heavily on a clean, high-contrast, data-dense interface. It uses a push-content sidebar structure with a sticky topbar. The primary color is a strong corporate blue (`#054d9e`) paired with a vibrant teal accent (`#18a999`).

## Colors
- **Primary**: `#054d9e`. Used for primary actions, active sidebar links, and accents.
- **Accent**: `#18a999`. Used for success states, highlights, and secondary buttons.
- **Backgrounds**: The main canvas is `#f0f4f8`. Surfaces (cards/panels) are pure white (`#ffffff`).
- **Text**: Primary text is `#182230` (Ink). Secondary text is `#64748b` (Muted).

## Components
- **Cards & Panels**: White background, 1px border (`#e2e8f0`), 12px border radius, and a subtle shadow.
- **Buttons**: Rounded (8px), with smooth hover transitions.
- **Sidenav**: Dark theme (`#0c1929` to `#0d1f36`), 260px wide, with a glowing active state indicator.
- **Topbar**: Sticky, white with 12px blur, housing a global search and notification badges.
- **Typography**: Strictly `Inter` font for all text to ensure maximum legibility for data-heavy views.
