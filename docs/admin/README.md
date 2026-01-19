# PA Penajam Website - Admin Documentation

## Overview

This documentation is for administrators managing the PA Penajam (Pengadilan Agama Penajam) website. The admin panel is built with Filament v5 and provides comprehensive tools for managing all website content.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Admin Panel Navigation](#admin-panel-navigation)
3. [Content Management](#content-management)
4. [Page Builder Guide](#page-builder-guide)
5. [Menu Management](#menu-management)
6. [News & Documents](#news--documents)
7. [Court Schedules](#court-schedules)
8. [Transparency & PPID](#transparency--ppid)
9. [User Management](#user-management)
10. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Accessing the Admin Panel

1. **URL:** Navigate to `https://your-domain.com/admin`
2. **Login:** Use your assigned credentials
3. **First Time:** You'll be prompted to complete your profile

### User Roles

The system has 5 role levels with different permissions:

| Role | Description | Permissions |
|------|-------------|-------------|
| **Super Admin** | Full system access | All permissions including user management |
| **Admin** | Administrative access | Most features except user management |
| **Author** | Content creator | Can create/edit content, publish with approval |
| **Designer** | Design & layout | Can use page builder, manage menus |
| **Subscriber** | Read-only | View content only |

### Admin Panel Dashboard

The dashboard shows:
- **Statistics:** Content counts, user activity
- **Recent Activity:** Latest changes across the system
- **Quick Actions:** Common tasks
- **System Status:** SIPP sync status, storage usage

---

## Admin Panel Navigation

### Sidebar Menu

The admin panel sidebar is organized into logical sections:

#### **Content Management**
- Pages - Manage website pages
- News - Articles and announcements
- Documents - File downloads
- Categories - Organize content

#### **Structure**
- Menus - Website navigation
- Page Builder - Visual page creation

#### **Court Data**
- Court Schedules - SIPP-integrated schedules
- Case Statistics - Court statistics
- Budget Transparency - Financial data

#### **PPID Portal**
- PPID Requests - Information requests

#### **System**
- Users - User management (Super Admin only)
- Settings - System configuration
- Joomla Migration - Data import tools

---

## Content Management

### Managing Pages

#### Creating a New Page

1. Navigate to **Content** → **Pages**
2. Click **"Create Page"** button
3. Fill in required fields:
   - **Title:** Page name (e.g., "Profil Pengadilan")
   - **Slug:** URL-friendly version (auto-generated from title)
   - **Status:** Draft or Published
   - **Content:** Use Page Builder or manual JSON
4. Set optional fields:
   - **Meta Description:** SEO description
   - **Template:** Choose from existing templates
   - **Published At:** Schedule publication
5. Click **"Save"**

#### Page Status Workflow

Pages follow this workflow:
1. **Draft** - Initial state, not visible publicly
2. **Published** - Visible on website
3. **Scheduled** - Will auto-publish at set date/time

#### Editing Pages

1. Go to **Pages** list
2. Click on the page title or **Edit** button
3. Make changes
4. Click **"Save"** to update

#### Page Templates

Templates allow you to save page layouts for reuse:
1. Create a page with Page Builder
2. Click **"Save as Template"**
3. Give it a name and description
4. Template available for all new pages

### Managing Categories

Categories organize your content (News, Documents):

1. Navigate to **Content** → **Categories**
2. Click **"Create Category"**
3. Fill in:
   - **Name:** Category name
   - **Slug:** URL-friendly version (auto-generated)
   - **Type:** Select type (News, Document, etc.)
   - **Parent:** Optional parent category for hierarchy
4. Click **"Save"**

---

## Page Builder Guide

### Overview

The Page Builder is a drag-and-drop interface for creating pages without coding.

### Accessing Page Builder

1. Create or edit a page
2. Click **"Launch Page Builder"** button
3. The builder interface opens with:
   - **Left Panel:** Component library
   - **Center:** Canvas/preview area
   - **Right Panel:** Component settings

### Available Components

#### **Text Components**
- **Heading** - H1-H6 headings
- **Paragraph** - Rich text paragraphs
- **Text Block** - Multi-line text with formatting

#### **Media Components**
- **Image** - Single image with caption
- **Gallery** - Image grid/gallery
- **Video** - Embed YouTube/Vimeo videos

#### **Layout Components**
- **Section** - Container for other blocks
- **Columns** - Multi-column layout (2-4 columns)
- **Divider** - Horizontal line/spacer

#### **Interactive Components**
- **Form** - Contact/feedback forms
- **Button** - Call-to-action buttons
- **Accordion** - Expandable content sections

#### **Court-Specific Components**
- **Schedule Widget** - Display court schedules
- **News Ticker** - Scrolling news announcements
- **Document List** - Featured documents

### Building a Page

#### Step 1: Add Components

1. **Drag** a component from the left panel to the canvas
2. **Drop** it where you want it to appear
3. Components auto-arrange vertically

#### Step 2: Configure Components

1. **Click** on a component to select it
2. **Right panel** shows component settings
3. **Modify** settings:
   - Text fields
   - Image uploads
   - Color/style options
   - Links and actions

#### Step 3: Arrange & Organize

1. **Drag** components to reorder
2. **Nest** components inside sections/columns
3. **Use** the layer panel to find hidden components

#### Step 4: Preview & Test

1. Click **"Preview"** to see how it looks
2. **Test** responsive views (mobile, tablet, desktop)
3. **Save** your changes

### Advanced Features

#### Component Styling

Each component has style options:
- **Colors:** Background, text, border colors
- **Spacing:** Padding, margins
- **Typography:** Font size, weight, alignment
- **Borders:** Rounded corners, border width

#### Responsive Design

- **Desktop Mode:** Default view
- **Tablet Mode:** 768px width
- **Mobile Mode:** 375px width
- Switch between modes to test responsiveness

#### Reusable Blocks

Save frequently used components:
1. Configure a component
2. Click **"Save as Block"**
3. Name it for future reference
4. Available in **"My Blocks"** section

### Best Practices

1. **Plan First:** Sketch your page layout before building
2. **Use Sections:** Group related content in sections
3. **Mobile-First:** Design for mobile first, then expand
4. **Consistency:** Use same styles across similar pages
5. **Test:** Always preview before publishing

### Common Tasks

#### Create a Landing Page

```
1. Hero Section
   - Heading component
   - Background image
   - CTA button

2. Features Section
   - 3-column layout
   - Icon + text in each column

3. About Section
   - Text block
   - Image gallery

4. Contact Section
   - Form component
   - Contact information
```

#### Create a Content Page

```
1. Page Title
   - Heading component (H1)

2. Main Content
   - Text blocks
   - Images interspersed
   - Document links

3. Related Content
   - News list component
   - Document list component
```

---

## Menu Management

### Understanding Menus

Menus control website navigation. You can create multiple menus for different locations:

- **Header Menu** - Top navigation
- **Footer Menu** - Footer links
- **Sidebar Menu** - Side navigation
- **Mobile Menu** - Mobile-specific menu

### Creating a Menu

1. Navigate to **Structure** → **Menus**
2. Click **"Create Menu"**
3. Fill in:
   - **Name:** Menu identifier (e.g., "Main Navigation")
   - **Location:** Where it appears (header, footer, etc.)
   - **Max Depth:** Maximum nesting level (default: 3)
4. Click **"Save"**

### Managing Menu Items

#### Adding Menu Items

1. Click on a menu name
2. Click **"Add Item"**
3. Configure the item:
   - **Title:** Display text
   - **URL Type:** Choose type:
     - **Route:** Link to Laravel route
     - **Page:** Link to a page
     - **Custom:** Manual URL
     - **External:** External website
   - **Target:** Open in same/new window
4. Click **"Save"**

#### Organizing Menu Items

Use the **Visual Menu Editor**:

1. Click **"Visual Editor"** tab
2. **Drag** items to reorder
3. **Indent** items to create sub-menus
4. **Drag** between menus to move
5. Changes auto-save

#### Menu Item Types

**1. Route Links**
- Connects to Laravel routes
- Example: `news.index`, `page.show`
- Automatically updates if route changes

**2. Page Links**
- Connects to Pages
- Auto-updates if page slug changes
- Shows page status indicator

**3. Custom URLs**
- Manual URL entry
- Full path required
- Use for special destinations

**4. External Links**
- Links to other websites
- Must include `https://`
- Opens in new tab by default

### Advanced Menu Features

#### Conditional Display

Show/hide menu items based on conditions:
- **User Role:** Display only for specific roles
- **Authentication:** Show/hide for logged-in users
- **Date Range:** Display only during specific period
- **Custom Logic:** PHP conditions

#### Menu Icons

Add icons to menu items:
1. Edit menu item
2. Click **"Add Icon"**
3. Choose from icon library
4. Select icon position (left/right)

#### Mega Menus

Create dropdown mega menus:
1. Set **Max Depth** to 2+
2. Add nested items
3. Configure column layout
4. Add content blocks

### Best Practices

1. **Keep It Simple:** Don't exceed 3 levels deep
2. **Clear Labels:** Use descriptive, short titles
3. **Logical Order:** Group related items
4. **Mobile Test:** Always test mobile menu
5. **Consistent:** Keep menu structure stable

---

## News & Documents

### Managing News

#### Creating News Articles

1. Navigate to **Content** → **News**
2. Click **"Create News"**
3. Fill in:
   - **Title:** Article title
   - **Slug:** URL-friendly version (auto-generated)
   - **Category:** Select category
   - **Content:** Article content (rich text editor)
   - **Featured:** Mark as featured (shows on homepage)
   - **Published At:** Schedule or publish immediately
4. Click **"Save"**

#### News Categories

Organize news by category:
1. Navigate to **Content** → **Categories**
2. Filter by **Type: News**
3. Create categories (e.g., "Berita", "Pengumuman", "Artikel")

#### Rich Text Editor Features

- **Text Formatting:** Bold, italic, underline
- **Headings:** H1-H6
- **Lists:** Ordered and unordered
- **Links:** Add/edit links
- **Images:** Upload or embed images
- **Tables:** Create tables
- **Media:** Embed videos
- **Code:** Insert code blocks

### Managing Documents

#### Uploading Documents

1. Navigate to **Content** → **Documents**
2. Click **"Create Document"**
3. Fill in:
   - **Title:** Document name
   - **Description:** Brief description
   - **File:** Upload file (PDF, DOC, XLS, etc.)
   - **Category:** Select document category
   - **Published At:** Schedule publication
   - **Is Public:** Show to public or logged-in users only
4. Click **"Save"**

#### Document Categories

Create categories for organization:
- **Putusan** - Court decisions
- **Surat Edaran** - Circular letters
- **Peraturan** - Regulations
- **Formulir** - Forms

#### Document Versions

Track document versions:
1. Upload new version of existing document
2. System auto-increments version number
3. Old versions archived
4. Users can download previous versions

### Bulk Operations

#### Bulk Actions (News & Documents)

1. Select multiple items (checkboxes)
2. Choose action:
   - **Publish** - Make visible
   - **Unpublish** - Hide from public
   - **Delete** - Remove permanently
   - **Change Category** - Move to different category
3. Click **"Apply"**

---

## Court Schedules

### About SIPP Integration

Court schedules are automatically synced from SIPP (Sistem Informasi Penelusuran Perkara) - the national case tracking system.

### Viewing Schedules

1. Navigate to **Court Data** → **Court Schedules**
2. View table with columns:
   - **Case Number** - Nomor perkara
   - **Case Title** - Judul perkara
   - **Judge** - Hakim yang menangani
   - **Room** - Ruang sidang
   - **Schedule Date** - Tanggal sidang
   - **Status** - Status perkara
3. Use filters to find specific schedules

### Managing Schedules

#### Sync Status

Check last sync time:
- **Last Sync:** Shows date/time of last successful sync
- **Next Sync:** Shows scheduled next sync
- **Sync Status:** Success/Failure indicator

#### Manual Sync

Trigger manual sync:
1. Click **"Sync Now"** button
2. System fetches latest data from SIPP API
3. Progress bar shows sync status
4. Log shows detailed sync information

#### Schedule Filters

Filter schedules by:
- **Date Range** - Specific dates
- **Judge** - Specific judge
- **Room** - Specific courtroom
- **Case Type** - Case category
- **Status** - Case status

### Case Statistics

View court statistics:
1. Navigate to **Court Data** → **Case Statistics**
2. View breakdown:
   - **Year/Month** - Time period
   - **Case Type** - Category
   - **Total Cases** - All cases
   - **Resolved** - Completed cases
   - **Pending** - Ongoing cases
   - **Average Duration** - Case processing time

### Troubleshooting SIPP Issues

#### Sync Failures

If sync fails:
1. Check **Sync Logs** for error messages
2. Verify SIPP API credentials
3. Check network connectivity
4. Contact technical support

#### Missing Data

If schedules are missing:
1. Check date range filters
2. Verify sync completed successfully
3. Check SIPP system availability
4. Review sync logs for errors

---

## Transparency & PPID

### Budget Transparency

#### Managing Budget Data

1. Navigate to **Court Data** → **Budget Transparency**
2. Click **"Create Budget Entry"**
3. Fill in:
   - **Year:** Budget year
   - **Title:** Budget item name
   - **Description:** Details
   - **Amount:** Budget amount (IDR)
   - **Document:** Upload supporting document
   - **Published At:** Publication date
4. Click **"Save"**

#### Display on Website

Budget data is displayed on:
- **Transparency Page** - `/transparansi`
- **Budget Section** - Breakdown by year
- **Downloads** - Supporting documents

### Case Statistics

#### Managing Statistics

1. Navigate to **Court Data** → **Case Statistics**
2. Click **"Create Statistics"**
3. Fill in:
   - **Year/Month:** Period
   - **Case Type:** Category
   - **Total Cases:** Number of cases
   - **Resolved:** Completed cases
   - **Pending:** Ongoing cases
   - **Average Duration:** Processing time (days)
4. Click **"Save"**

### PPID Portal

#### Understanding PPID

PPID (Pejabat Pengelola Informasi dan Dokumentasi) is the information access service required by law.

#### Managing PPID Requests

1. Navigate to **PPID Portal** → **PPID Requests**
2. View all requests with:
   - **Requester Name** - Name of requester
   - **Request Type** - Category of request
   - **Description** - What they're asking for
   - **Status** - Current status
   - **Created At** - Date submitted

#### Request Workflow

Requests follow this workflow:

1. **Pending** (Baru)
   - Request submitted
   - Awaiting admin review

2. **Processed** (Diproses)
   - Admin is working on it
   - Gathering information

3. **Completed** (Selesai)
   - Response provided
   - Request closed

4. **Rejected** (Ditolak)
   - Request cannot be fulfilled
   - Reason provided

#### Responding to Requests

1. Click on a request
2. Review the details
3. Type response in **Response** field
4. Attach documents if needed
5. Change status to **Processed** or **Completed**
6. Click **"Save"**
7. System emails requester with update

#### PPID Categories

Request types include:
- **Informasi Berkala** - Regularly published information
- **Informasi Serta Merta** - Immediate information
- **Informasi Setiap Saat** - Information available anytime
- **Informasi Yang Dikecualikan** - Exempt information

---

## User Management

> **Note:** Only Super Admins can access User Management

### Viewing Users

1. Navigate to **System** → **Users**
2. View all registered users
3. Filter by:
   - **Role** - User role
   - **Status** - Active/Inactive
   - **Last Login** - Recent activity

### Creating Users

1. Click **"Create User"**
2. Fill in:
   - **Name:** Full name
   - **Email:** Email address (unique)
   - **Password:** Initial password
   - **Role:** Assign role
   - **Permissions:** Custom permissions (optional)
3. Click **"Save"**
4. System sends welcome email to user

### Managing User Roles

#### Changing User Role

1. Click on user name
2. Change **Role** dropdown
3. Click **"Save"**
4. User permissions update immediately

#### Custom Permissions

Grant specific permissions:
1. Edit user
2. Expand **Permissions** section
3. Check specific permissions
4. Click **"Save"**

### User Activity

Monitor user activity:
- **Last Login:** Most recent login
- **Activity Log:** Recent actions
- **Content Count:** Items created/edited
- **Profile Completed:** Is profile complete

---

## Troubleshooting

### Common Issues & Solutions

#### Page Builder Not Loading

**Problem:** Page Builder won't open

**Solutions:**
1. Clear browser cache
2. Check internet connection
3. Try different browser
4. Check browser console for errors
5. Contact technical support

#### Menu Not Showing on Website

**Problem:** Created menu not visible

**Solutions:**
1. Check menu **Location** setting
2. Verify menu items are **Active**
3. Clear website cache
4. Check user role permissions
5. Inspect page for errors

#### Images Not Uploading

**Problem:** Can't upload images

**Solutions:**
1. Check file size (max 5MB)
2. Verify file type (JPG, PNG, GIF, WEBP)
3. Check storage permissions
4. Clear browser cache
5. Try different file

#### SIPP Sync Failing

**Problem:** Court schedules not syncing

**Solutions:**
1. Check **Sync Logs** for errors
2. Verify SIPP API credentials in Settings
3. Check network connectivity
4. Try manual sync
5. Contact technical support

#### Can't Publish Content

**Problem:** No publish button visible

**Solutions:**
1. Check your role permissions
2. Contact admin for approval
3. Save as Draft instead
4. Verify content is complete

#### Website Shows Old Content

**Problem:** Changes not visible on website

**Solutions:**
1. Clear browser cache (Ctrl+F5)
2. Clear application cache
3. Check content **Status** is Published
4. Verify **Published At** date is in past
5. Wait 1-2 minutes for cache refresh

### Getting Help

#### Internal Support

1. **Technical Support:** Contact IT department
2. **Content Issues:** Contact Content Manager
3. **Access Issues:** Contact Super Admin

#### Resources

- **Admin Documentation:** This guide
- **Training Videos:** [Link to video library]
- **FAQ:** Frequently Asked Questions section
- **Release Notes:** System updates and features

### Error Messages

#### Common Error Messages

**"Unauthorized Access"**
- You don't have permission
- Contact your admin

**"Validation Failed"**
- Check required fields (marked with *)
- Fix form errors
- Try again

**"Server Error"**
- Try again in a few minutes
- Contact technical support if persists

**"File Upload Failed"**
- Check file size and type
- Try different file
- Check internet connection

---

## Best Practices

### Content Management

1. **Plan Before Creating**
   - Outline content structure
   - Prepare images and documents
   - Set publication schedule

2. **Use Categories Effectively**
   - Create logical category structure
   - Don't create too many categories
   - Keep naming consistent

3. **Optimize Images**
   - Compress images before upload
   - Use appropriate dimensions
   - Add alt text for accessibility

4. **Write Good Titles**
   - Clear and descriptive
   - Include keywords
   - Keep under 60 characters

5. **Preview Before Publishing**
   - Always preview changes
   - Test on mobile devices
   - Check all links

### Security

1. **Strong Passwords**
   - Use complex passwords
   - Change regularly
   - Don't share passwords

2. **Log Out**
   - Always log out when done
   - Don't save passwords in browser
   - Use private mode on shared devices

3. **Report Issues**
   - Report suspicious activity
   - Report bugs immediately
   - Don't ignore error messages

### Performance

1. **Optimize Images**
   - Use WebP format
   - Compress before upload
   - Limit image dimensions

2. **Limit Page Blocks**
   - Don't overload pages
   - Use pagination for lists
   - Lazy load images

3. **Regular Maintenance**
   - Review old content
   - Archive outdated items
   - Clean up unused media

---

## Glossary

| Term | Definition |
|------|------------|
| **Page Builder** | Visual drag-and-drop page editor |
| **SIPP** | Sistem Informasi Penelusuran Perkara |
| **PPID** | Pejabat Pengelola Informasi dan Dokumentasi |
| **Filament** | Admin panel framework |
| **Slug** | URL-friendly version of title |
| **Rich Text Editor** | WYSIWYG text editor |
| **Hero Section** | Top section of a landing page |
| **CTA** | Call-to-Action button |
| **SEO** | Search Engine Optimization |
| **Meta Description** | Brief page description for search engines |

---

## Quick Reference

### Keyboard Shortcuts

- **Ctrl + S** - Save
- **Ctrl + Z** - Undo
- **Ctrl + Y** - Redo
- **Ctrl + B** - Bold (in editor)
- **Ctrl + I** - Italic (in editor)

### Common Paths

- **Admin Panel:** `/admin`
- **Pages:** `/admin/pages`
- **News:** `/admin/news`
- **Documents:** `/admin/documents`
- **Menus:** `/admin/menus`

### File Upload Limits

- **Images:** 5MB max
- **Documents:** 20MB max
- **Videos:** 100MB max (if enabled)

### Supported Formats

- **Images:** JPG, PNG, GIF, WEBP, SVG
- **Documents:** PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX
- **Videos:** MP4, WEBM (if enabled)

---

## Updates & Changelog

### Version 1.0.0 (Current)
- Initial release
- Page Builder
- Menu Management
- News & Documents
- SIPP Integration
- PPID Portal
- User Management

### Future Updates
- [ ] Enhanced analytics
- [ ] Advanced SEO tools
- [ ] Multi-language support
- [ ] Mobile app integration

---

## Contact & Support

### Technical Support
- **Email:** tech-support@pa-penajam.go.id
- **Phone:** [Phone number]
- **Hours:** Monday-Friday, 8:00-16:00 WIB

### Training Requests
- **Email:** training@pa-penajam.go.id
- **Schedule:** Monthly training sessions

### Bug Reports
- **Email:** bugs@pa-penajam.go.id
- **Form:** [Internal bug report form]

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
**Next Review:** 2026-07-18
