---
name: IMS Core
colors:
  surface: '#FFFFFF'
  surface-dim: '#d0daee'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff3ff'
  surface-container: '#e6eeff'
  surface-container-high: '#dfe9fc'
  surface-container-highest: '#d9e3f6'
  on-surface: '#121c2a'
  on-surface-variant: '#424751'
  inverse-surface: '#273140'
  inverse-on-surface: '#ebf1ff'
  outline: '#737783'
  outline-variant: '#c2c6d3'
  surface-tint: '#245dae'
  primary: '#003775'
  on-primary: '#ffffff'
  primary-container: '#054d9e'
  on-primary-container: '#a3c1ff'
  inverse-primary: '#abc7ff'
  secondary: '#006b60'
  on-secondary: '#ffffff'
  secondary-container: '#7df7e4'
  on-secondary-container: '#007166'
  tertiary: '#622600'
  on-tertiary: '#ffffff'
  tertiary-container: '#863700'
  on-tertiary-container: '#ffae86'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d7e2ff'
  primary-fixed-dim: '#abc7ff'
  on-primary-fixed: '#001b3f'
  on-primary-fixed-variant: '#004590'
  secondary-fixed: '#7df7e4'
  secondary-fixed-dim: '#5edac8'
  on-secondary-fixed: '#00201c'
  on-secondary-fixed-variant: '#005048'
  tertiary-fixed: '#ffdbcb'
  tertiary-fixed-dim: '#ffb692'
  on-tertiary-fixed: '#341100'
  on-tertiary-fixed-variant: '#793100'
  background: '#f8f9ff'
  on-background: '#121c2a'
  surface-variant: '#d9e3f6'
  canvas: '#F0F4F8'
  border: '#E2E8F0'
  muted: '#64748B'
  sidebar-dark: '#0C1929'
  danger: '#DC2626'
  warning: '#EA580C'
  success: '#16A34A'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.03em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  sidebar-width: 260px
  container-max: 1440px
  gutter: 24px
  margin-mobile: 16px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 24px
---

## Brand & Style
The design system is engineered for the ITOP Management System (IMS), focusing on a **Corporate Modern** aesthetic that prioritizes high information density and operational clarity. The brand personality is authoritative, systematic, and efficient, designed to reduce cognitive load for IT professionals managing complex infrastructures. 

The visual language utilizes a "Surface-on-Canvas" approach: pure white interactive surfaces are layered over a cool-toned, structural background. High contrast is maintained through deep ink-colored typography and a signature corporate blue, while a vibrant teal provides energetic highlights for secondary actions and success states.

## Colors
The color architecture is divided into structural zones. 

- **Primary Blue (#054D9E)**: Reserved for primary signals, main navigation active states, and critical action buttons.
- **Accent Teal (#18A999)**: Used for functional highlights, success indicators, and secondary navigation elements to provide a distinct visual departure from the primary blue.
- **The Grayscale**: Utilizes "Ink" (#182230) for maximum legibility in body text. Backgrounds use a light blue-gray "Canvas" (#F0F4F8) to create a distinct separation from white "Surface" cards.
- **Sidebar**: A deep, low-light navy (#0C1929) provides a solid anchor for the navigation, contrasting against the light main content area.

## Typography
This design system relies exclusively on **Inter** to leverage its exceptional legibility in data-dense environments. 

- **Scale**: A tight typographic scale is used to maximize screen real estate. 14px is the standard for body text, while 13px and 12px are used for dense data tables and sidebars.
- **Hierarchy**: Weight is the primary driver of hierarchy. Headlines use Semi-Bold (600) or Bold (700), while labels use Medium (500) to remain clear at small sizes.
- **Letter Spacing**: Slight negative tracking is applied to large headings to maintain a compact, professional appearance.

## Layout & Spacing
The layout follows a **Fixed-Fluid hybrid grid**:
- **Sidebar**: A fixed 260px dark-themed navigation panel.
- **Main Content**: A fluid area that stretches to a maximum of 1440px, centered on larger displays to prevent line lengths from becoming unreadable.
- **Grid model**: A 12-column grid with 24px gutters. For data dashboards, cards should typically span 3, 4, 6, or 12 columns.
- **Density**: Use an 8px spacing rhythm. In data tables, reduce internal cell padding to 8px (vertical) to increase visible row count.

## Elevation & Depth
Depth is conveyed through **Low-Contrast Outlines** and subtle environmental shadows:
- **Level 0 (Canvas)**: Background color (#F0F4F8). No elevation.
- **Level 1 (Cards/Panels)**: White surface, 1px border (#E2E8F0), and a very soft shadow (0px 2px 4px rgba(0,0,0,0.05)).
- **Level 2 (Dropdowns/Modals)**: White surface, 1px border, and a more pronounced shadow (0px 10px 15px rgba(0,0,0,0.1)) to indicate focus.
- **Sticky Topbar**: Pure white with a `backdrop-filter: blur(12px)` and a subtle bottom border to maintain separation from scrolling content.

## Shapes
The shape language is structured to feel "soft-enterprise":
- **Base Corner Radius**: 12px (rounded-lg) is the standard for all primary containers, cards, and panels.
- **Component Radius**: 8px (rounded-md) for buttons, input fields, and chips, creating a slightly tighter look for interactive elements.
- **Small Radius**: 4px for utility elements like tooltips or checkbox containers.

## Components
- **Buttons**: Primary buttons use the corporate blue with white text. Secondary buttons use the teal accent. All buttons should have an 8px radius and a subtle hover transition that darkens the background color by 10%.
- **Cards**: Must include a 12px radius, white background, and a 1px border (#E2E8F0). Header sections within cards should have a subtle bottom border.
- **Sidenav**: Navigation links should have a "glowing" active state—a vertical 4px bar on the left edge using the teal accent color and a subtle background highlight.
- **Input Fields**: 8px radius, white background, and a 1px border. On focus, the border shifts to primary blue with a 2px soft outer glow.
- **Chips/Badges**: Use the "light" variants of the status colors (e.g., success-light) with high-contrast text for status indicators in tables.
- **Data Tables**: Use zebra-striping with #F4F7FB on hover. Headers should be sticky with a Medium (500) weight font in the "Muted" color.