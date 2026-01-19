# PA Penajam Website Documentation

## Overview

Welcome to the comprehensive documentation for the PA Penajam (Pengadilan Agama Penajam) website. This system is built with Laravel 12, Inertia.js v2, React 19, and Filament v5, providing a modern court information management system.

---

## Quick Links

### For New Users
- **[Quick Start Guide](training/QUICK_START.md)** - Get started in 30 minutes
- **[Admin Documentation](admin/README.md)** - Comprehensive admin guide
- **[Troubleshooting Guide](admin/TROUBLESHOOTING.md)** - Solve common issues

### For Developers
- **[Technical Architecture](technical/ARCHITECTURE.md)** - System design and patterns
- **[API Documentation](technical/API_DOCUMENTATION.md)** - REST API endpoints
- **[Database ERD](technical/DATABASE_ERD.md)** - Database schema documentation
- **[Deployment Guide](technical/DEPLOYMENT_GUIDE.md)** - Production deployment

### For Trainers
- **[Training Materials](training/TRAINING_MATERIALS.md)** - Role-based training curriculum

---

## Documentation Structure

```
docs/
├── README.md (this file)
│
├── admin/                      # Administrator & User Guides
│   ├── README.md               # Comprehensive admin documentation
│   └── TROUBLESHOOTING.md      # Common issues and solutions
│
├── technical/                  # Technical Documentation
│   ├── ARCHITECTURE.md         # System architecture overview
│   ├── API_DOCUMENTATION.md    # API endpoints and usage
│   ├── DATABASE_ERD.md         # Database schema and relationships
│   └── DEPLOYMENT_GUIDE.md     # Production deployment guide
│
└── training/                   # Training & Education
    ├── QUICK_START.md          # New user quick start
    └── TRAINING_MATERIALS.md   # Comprehensive training materials
```

---

## Getting Started

### I'm a New User

**Start here:** [Quick Start Guide](training/QUICK_START.md)

This 15-minute guide will help you:
- Log in for the first time
- Understand the dashboard
- Create your first page
- Publish your first news article
- Upload documents

**Next Steps:**
- Read [Admin Documentation](admin/README.md) for detailed features
- Watch training videos (coming soon)
- Complete hands-on exercises

### I'm a Content Author

**Essential Reading:**
1. [Quick Start Guide](training/QUICK_START.md) - Basic navigation and concepts
2. [Admin Documentation - Content Management](admin/README.md#content-management) - Creating and editing content
3. [Admin Documentation - Page Builder](admin/README.md#page-builder-guide) - Visual page creation

**Common Tasks:**
- Creating Pages → [Admin Docs](admin/README.md#managing-pages)
- Publishing News → [Admin Docs](admin/README.md#managing-news)
- Uploading Documents → [Admin Docs](admin/README.md#managing-documents)

### I'm a Designer

**Essential Reading:**
1. [Quick Start Guide](training/QUICK_START.md) - System overview
2. [Admin Documentation - Page Builder](admin/README.md#page-builder-guide) - Building pages
3. [Admin Documentation - Menu Management](admin/README.md#menu-management) - Navigation design

**Common Tasks:**
- Building Page Layouts → [Page Builder Guide](admin/README.md#page-builder-guide)
- Creating Menus → [Menu Management](admin/README.md#menu-management)
- Designing Templates → [Training Materials](training/TRAINING_MATERIALS.md#role-2-designer)

### I'm an Administrator

**Essential Reading:**
1. [Admin Documentation](admin/README.md) - Complete admin guide
2. [Technical Architecture](technical/ARCHITECTURE.md) - System overview
3. [Troubleshooting Guide](admin/TROUBLESHOOTING.md) - Issue resolution

**Common Tasks:**
- User Management → [Admin Docs](admin/README.md#user-management)
- System Configuration → [Admin Docs](admin/README.md#system-settings)
- Performance Monitoring → [Technical Docs](technical/ARCHITECTURE.md#monitoring)

### I'm a Developer

**Essential Reading:**
1. [Technical Architecture](technical/ARCHITECTURE.md) - System design and patterns
2. [API Documentation](technical/API_DOCUMENTATION.md) - API endpoints
3. [Database ERD](technical/DATABASE_ERD.md) - Database schema
4. [Deployment Guide](technical/DEPLOYMENT_GUIDE.md) - Deployment procedures

**Common Tasks:**
- Understanding Codebase → [Architecture](technical/ARCHITECTURE.md)
- Building API Integrations → [API Docs](technical/API_DOCUMENTATION.md)
- Database Queries → [Database ERD](technical/DATABASE_ERD.md)
- Deploying to Production → [Deployment Guide](technical/DEPLOYMENT_GUIDE.md)

---

## System Overview

### Technology Stack

**Backend:**
- Laravel 12 (PHP 8.5+)
- Filament v5 (Admin Panel)
- MySQL 8+ / SQLite (Development)

**Frontend:**
- Inertia.js v2
- React 19 + TypeScript
- Tailwind CSS v4
- shadcn/ui Components

**Development:**
- Pest v4 (Testing)
- Laravel Pint (Code Style)
- Vite (Build Tool)

### Key Features

- **Page Builder** - Drag-and-drop page creation
- **Dynamic Menus** - Visual menu management
- **News System** - Articles and announcements
- **Document Management** - File uploads with versioning
- **Court Schedules** - SIPP integration for court data
- **PPID Portal** - Public information requests
- **Transparency Data** - Budget and case statistics

### User Roles

| Role | Permissions |
|------|-------------|
| **Super Admin** | Full system access including user management |
| **Admin** | Administrative features except user management |
| **Author** | Create/edit content, publish with approval |
| **Designer** | Page builder, menu management, templates |
| **Subscriber** | Read-only access |

---

## Learning Path

### Beginner (New Users)

**Week 1: Foundation**
- [ ] Complete [Quick Start Guide](training/QUICK_START.md)
- [ ] Create your first page
- [ ] Publish your first news article
- [ ] Upload a document
- [ ] Navigate all admin sections

**Week 2: Content Creation**
- [ ] Master Page Builder
- [ ] Create complex page layouts
- [ ] Work with categories
- [ ] Schedule content publication

**Week 3: Advanced Features**
- [ ] Manage menus
- [ ] Respond to PPID requests
- [ ] Review court schedules
- [ ] Understand SIPP integration

### Intermediate (Content Authors & Designers)

**Month 1: Mastery**
- [ ] Complete all [Training Exercises](training/TRAINING_MATERIALS.md#hands-on-exercises)
- [ ] Create page templates
- [ ] Build complex menu structures
- [ ] Optimize images and media
- [ ] Implement responsive designs

**Month 2: Advanced**
- [ ] Master all Page Builder components
- [ ] Create reusable templates
- [ ] Optimize performance
- [ ] Troubleshoot common issues

### Advanced (Administrators & Developers)

**Quarter 1: System Administration**
- [ ] User and permission management
- [ ] System configuration
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Backup and restore procedures

**Quarter 2: Development & Integration**
- [ ] Understand [Technical Architecture](technical/ARCHITECTURE.md)
- [ ] Work with [API endpoints](technical/API_DOCUMENTATION.md)
- [ ] Query [database](technical/DATABASE_ERD.md) efficiently
- [ ] Deploy to [production](technical/DEPLOYMENT_GUIDE.md)

---

## Support & Resources

### Getting Help

1. **Self-Service**
   - Search documentation (use Ctrl+F)
   - Read [Troubleshooting Guide](admin/TROUBLESHOOTING.md)
   - Review [FAQ](admin/README.md#faq)

2. **Peer Support**
   - Ask colleagues
   - Contact your team lead
   - Check team communication channels

3. **Official Support**
   - Email: tech-support@pa-penajam.go.id
   - Phone: [Insert phone number]
   - Hours: Monday-Friday, 8:00-16:00 WIB

### Training Opportunities

**Monthly Training Sessions:**
- **Basic Training** - First Monday of each month
- **Advanced Training** - Third Monday of each month
- **Q&A Session** - Last Friday of each month

**To Register:**
- Email: training@pa-penajam.go.id
- Include: Name, Role, Preferred Session

### Online Resources

- **Video Tutorials** (Coming Soon)
- **Interactive Tutorials** (Coming Soon)
- **Knowledge Base** (Coming Soon)
- **Community Forum** (Coming Soon)

---

## Documentation Versions

### Current Version: 1.0.0

**Release Date:** 2026-01-18

**This Version Includes:**
- Complete admin documentation
- Technical architecture documentation
- API reference
- Database ERD
- Deployment guide
- Training materials
- Troubleshooting guide

### Future Updates

Planned documentation updates:
- [ ] Video tutorials
- [ ] Interactive guides
- [ ] API integration examples
- [ ] Advanced customization guide
- [ ] Performance tuning guide
- [ ] Security hardening guide

---

## Contributing to Documentation

### Reporting Issues

Found an error or gap in documentation?

1. **Check for Existing Issues**
   - Review known issues
   - Search for similar reports

2. **Report the Issue**
   - Email: docs@pa-penajam.go.id
   - Include: Location, Issue, Suggested Fix

3. **Provide Context**
   - What were you trying to do?
   - What was confusing?
   - What would help clarify?

### Suggesting Improvements

Have ideas for better documentation?

1. **Identify the Need**
   - Missing information?
   - Unclear explanations?
   - Better examples needed?

2. **Submit Suggestion**
   - Email: docs@pa-penajam.go.id
   - Include: Section, Improvement Idea, Use Case

3. **Provide Examples**
   - Real-world scenarios
   - Specific use cases
   - Sample code or screenshots

---

## FAQ

### General Questions

**Q: Where do I start?**
A: Start with the [Quick Start Guide](training/QUICK_START.md) for a 15-minute introduction.

**Q: How do I reset my password?**
A: Click "Forgot Password" on the login page and follow the instructions.

**Q: What browser should I use?**
A: Chrome, Firefox, Safari, or Edge (latest versions). Internet Explorer is not supported.

**Q: Can I access the admin panel on mobile?**
A: Yes, the admin panel is responsive, but we recommend using a desktop or tablet for best experience.

### Technical Questions

**Q: What are the system requirements?**
A: See [Technical Architecture](technical/ARCHITECTURE.md#server-requirements) for details.

**Q: How do I report a bug?**
A: Email tech-support@pa-penajam.go.id with details and screenshots.

**Q: Is there an API available?**
A: Yes, see the [API Documentation](technical/API_DOCUMENTATION.md) for details.

### Content Questions

**Q: How do I create a page?**
A: See the [Managing Pages](admin/README.md#managing-pages) section.

**Q: What's the Page Builder?**
A: A drag-and-drop tool for creating pages without coding. See the [Page Builder Guide](admin/README.md#page-builder-guide).

**Q: How do I add images?**
A: Use the Image component in Page Builder or upload to Documents library.

---

## Changelog

### Version 1.0.0 (2026-01-18)

**Added:**
- Initial documentation release
- Admin user guide
- Technical architecture documentation
- API documentation
- Database ERD
- Deployment guide
- Training materials
- Troubleshooting guide

**Features:**
- Comprehensive table of contents
- Quick links for different user roles
- Learning paths for beginners to advanced
- Support and resources section
- FAQ section

---

## License & Copyright

**Copyright:** © 2026 Pengadilan Agama Penajam

**Documentation License:** Internal use only

**System License:** Proprietary

---

## Contact

**Documentation Team:**
- Email: docs@pa-penajam.go.id

**Technical Support:**
- Email: tech-support@pa-penajam.go.id
- Phone: [Insert phone number]

**Training Coordination:**
- Email: training@pa-penajam.go.id

---

**Last Updated:** 2026-01-18
**Document Version:** 1.0.0
**System Version:** Laravel 12
