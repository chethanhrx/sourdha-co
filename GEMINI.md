# Divyachethana Souhardha Sahakari Sangha Website

Official website for **Divyachethana Souhardha Sahakari Sangha Niyamitha**, a cooperative society based in Thirthahalli. The project is a feature-rich static website designed to provide information about the society's schemes, loans, and services in both Kannada and English.

## Project Overview

- **Technologies:** HTML5, CSS3 (Vanilla), JavaScript (Vanilla).
- **Core Features:**
  - **Multi-language Support:** Primary language is Kannada. English support is provided through a combination of custom logic (for logos/UI) and Google Translate integration.
  - **Interest Calculator:** Interactive tool for calculating FD (Fixed Deposit) and RD (Recurring Deposit) returns.
  - **Notification System:** A dynamic carousel for announcements and updates, supporting both hardcoded and admin-defined notifications.
  - **Responsive Design:** Fully mobile-friendly with a dedicated mobile menu and optimized layouts.
  - **Admin Interface:** Includes an `admin.html` for managing site content like notifications.

## Project Structure

```text
/
├── index.html              # Main landing page
├── admin.html              # Administrative interface
├── documents.html          # Society documents and forms
├── other-businesses.html   # Information on additional ventures
├── css/
│   └── style.css           # Centralized stylesheet with CSS variables
├── js/
│   └── main.js             # Core logic (Lang switching, Calculator, UI)
└── assets/
    ├── images/             # Logos, banners, and gallery photos
    └── videos/             # Promotional or informational videos
```

## Building and Running

As a static website, this project does not require a build step.

- **Development:** Open `index.html` in any modern web browser.
- **Production:** Deploy the root directory to any static hosting service (e.g., GitHub Pages, Netlify, Vercel).

## Development Conventions

- **Language:** Kannada is the primary content language. Use `UTF-8` encoding and ensure Noto Sans Kannada is loaded.
- **Styling:**
  - Use the CSS variables defined in `:root` (in `style.css`) for consistent colors, fonts, and spacing.
  - Follow the established naming convention for classes (e.g., `.btn-p`, `.hero-kn`, `.nc-card`).
- **JavaScript:**
  - Prefer Vanilla JS over libraries for performance.
  - Logic for UI components (carousels, calculators) should reside in `js/main.js`.
  - Use `localStorage` for persistent client-side state (like language preference `dc_lang`).
- **Assets:** Ensure all images are optimized for web use and placed in the appropriate `assets/` subfolder.
