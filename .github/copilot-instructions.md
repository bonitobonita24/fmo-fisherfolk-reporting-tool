# Copilot Instructions - Fisherfolk ID Database Charts

## Project Overview
This project provides data visualization and charting capabilities for the Fisherfolk Identification Database system for Calapan City, developed by Powerbyte IT Solutions.

## Architecture & Structure
<!-- Update this section as the project develops -->
- **Data Layer**: MySQL/PostgreSQL database for storing fisherfolk data
- **Chart Components**: Document chart types used (e.g., demographics, number of fisherfolk per barangay, number of fisherfolk by age group, number of fisherfolk by gender, number of fisherfolk by category, )
- **UI Framework**: Tailwind CSS / Bootstrap / Material-UI =
- **Frontend Framework**: React / Vue.js / Angular
- **Stack**: LAMP
NOTE: I only have a LAMP server available for deployment, please consider this when suggesting technologies and that I can run backend code (PHP) and frontend code (HTML, CSS, JS) on the server.

## Development Workflow
<!-- Add specific commands once established -->
```bash
# Installation
# npm install / pip install -r requirements.txt / etc.

# Development server
# npm run dev / python app.py / etc.

# Build
# npm run build / etc.

# Testing
# npm test / pytest / etc.
```

## Code Conventions
<!-- Document as patterns emerge -->
- **File naming**: TBD (e.g., `kebab-case.js`, `PascalCase.tsx`)
- **Component structure**: TBD
- **Data formatting**: Ensure dates follow ISO 8601, numbers use appropriate locale formatting

## Data & API Guidelines
<!-- Specify once data sources are integrated -->
- **Fisherfolk ID format**: consist of alphanumeric characters, length 8-12 with common data fields such as:
1. ID Number (unique)
2. Full Name
3. Date of Birth
4. Address (Barangay)
5. Sex
6. Image (URL or file path with image preview capability)
7. Signature (URL or file path with image preview capability)
8. RSBSA Number
9. Category ( with multiple select capability of the following:
 Boat Owner/Operator 
 Capture Fishing
 Gleaning, Vendor, Fish Processing, Aquaculture)
 Vendor
 Fish Processing
 Aquaculture)
10. Contact Number
11. Date Registered
12. Date Updated


- **Database schema**: 
  id_number VARCHAR(50) PRIMARY KEY,
  full_name VARCHAR(255),
  date_of_birth DATE,
  address VARCHAR(255),
  sex VARCHAR(10),
  image VARCHAR(255),
  signature VARCHAR(255),
  rsbsa VARCHAR(50),
  category VARCHAR(100),
  contact_number VARCHAR(20),
  boat_owneroperator TINYINT(1) DEFAULT 0,
  capture_fishing TINYINT(1) DEFAULT 0,
  gleaning TINYINT(1) DEFAULT 0,
  vendor TINYINT(1) DEFAULT 0,
  fish_processing TINYINT(1) DEFAULT 0,
  aquaculture TINYINT(1) DEFAULT 0

## Chart Implementation
<!-- Document as charts are created -->
- **Library used**: TBD (Chart.js, D3.js, Recharts, Plotly, etc.)
- **Standard chart types**: TBD (bar, line, pie, geographic)
- **Color scheme**: Orange (#FFA500) and Blue (#0000FF) to reflect maritime theme
- **Responsive behavior**: Document breakpoints and mobile handling

## External Dependencies
<!-- List once dependencies are added -->
- server: 127.0.0.1
- username: root
- password: 4,q@TG^Gy.HzM%ZL-B

## Key Files & Directories
<!-- Update as project structure develops -->
```
/
├── src/              # Source code
├── data/             # Sample or cached data
├── charts/           # Chart components
└── config/           # Configuration files
```

## Domain-Specific Context
- **Powerbyte IT Solutions**: Development partner for Calapan City fisherfolk management system
- **Fisherfolk data types**: Demographics, vessel registration, catch reports, licensing
- **Reporting requirements**: Document any specific government reporting formats

---
*Update these instructions as the codebase evolves to reflect actual patterns and practices.*
