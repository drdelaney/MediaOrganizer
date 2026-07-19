# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
This is a project for Media Organization. It is designed to replace Griffith, an older application which is no longer mantained.

### Web Application Structure (www/)
```
/
├── app/                  # CodeIgniter 4 application
│   ├── Controllers/     # MVC Controllers
│   │   └── *.php       # Web controllers
│   ├── Models/         # Database models
│   ├── Views/          # Template files
│   ├── Libraries/      # Custom libraries
│   ├── Config/         # Application configuration
│   │   └── Routes.php  # Routing definitions
├── public/             # Web root (document root)
│   ├── *.phtml         # Legacy direct-access files
│   ├── *.php           # Legacy direct-access files
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript libraries
│   └── index.php       # CodeIgniter entry point
└── system/             # CodeIgniter 4 framework
```
### Framework Architecture
1. **CodeIgniter 4 Layer** (`app/`)
    - Modern MVC controllers, models, views
    - Resource routing and auto-routing

## Environment Requirements
- PHP 8.2 with `intl`, `mbstring`, `mysqli` extensions
- MariaDB/MySQL database
- Web server with HTTP/HTTPS

## Required Reading
Claude Code should automatically read the following supplementary documentation files before proceeding with any tasks:
- `./ai_docs/coding_standards.md` - Project coding standards
- `./ai_docs/best_practices.md` - Development best practices


## Additional Documentation
- **Coding Standards**: Refer to [coding_standards.md](./ai_docs/coding_standards.md) for project-specific coding guidelines
- **Best Practices**: See [best_practices.md](./ai_docs/best_practices.md) for development guidelines and principles