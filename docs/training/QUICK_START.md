# PA Penajam Website - Quick Start Guide

## Welcome to the PA Penajam Website Admin Panel

This guide will help you get started with managing the PA Penajam website. Whether you're a content author, designer, or administrator, this guide covers the essential tasks you'll need.

---

## Table of Contents

1. [First Login](#first-login)
2. [Dashboard Overview](#dashboard-overview)
3. [Creating Your First Page](#creating-your-first-page)
4. [Managing News](#managing-news)
5. [Uploading Documents](#uploading-documents)
6. [Managing Menus](#managing-menus)
7. [Next Steps](#next-steps)

---

## First Login

### Accessing the Admin Panel

1. **Open your browser** and navigate to:
   ```
   https://pa-penajam.go.id/admin
   ```

2. **Enter your credentials:**
   - **Email:** Your registered email address
   - **Password:** Your password

3. **Click "Login"** button

### First Time Setup

If this is your first time logging in, you'll see a **Setup Wizard**:

1. **Complete your profile:**
   - Add your full name
   - Upload a profile photo (optional)
   - Set your timezone

2. **Change your password** (recommended):
   - Use a strong password
   - Mix letters, numbers, and symbols
   - At least 8 characters

3. **Complete the wizard** to access the dashboard

---

## Dashboard Overview

After logging in, you'll see the **Dashboard** with:

### Quick Stats
- **Total Pages:** Number of published pages
- **Total News:** News articles published
- **Total Documents:** Documents uploaded
- **Recent Activity:** Latest changes

### Navigation Menu (Left Sidebar)

The sidebar is organized into sections:

#### **Content** (Manage website content)
- 📄 **Pages** - Website pages
- 📰 **News** - Articles and announcements
- 📁 **Documents** - Downloadable files
- 🏷️ **Categories** - Content organization

#### **Structure** (Website structure)
- 🗺️ **Menus** - Navigation menus
- 🎨 **Page Builder** - Visual page editor

#### **Court Data** (Court-specific content)
- 📅 **Court Schedules** - SIPP-integrated schedules
- 📊 **Case Statistics** - Court statistics
- 💰 **Budget Transparency** - Financial data

#### **PPID Portal** (Information service)
- 📋 **PPID Requests** - Public information requests

#### **System** (Administration)
- 👥 **Users** - User management (Admin only)
- ⚙️ **Settings** - System configuration

---

## Creating Your First Page

Let's create a simple "About Us" page:

### Step 1: Navigate to Pages

1. Click **"Pages"** in the sidebar
2. You'll see a list of existing pages

### Step 2: Create New Page

1. Click the **"Create Page"** button (top right)
2. A form will appear

### Step 3: Fill in Page Details

**Required Fields:**

- **Title:** `Tentang Kami` (About Us)
  - The slug will auto-generate as `tentang-kami`

- **Status:** Choose `Draft` or `Published`
  - Start with `Draft` if you're not ready to publish

**Optional Fields:**

- **Meta Description:** Brief description for search engines
  - Example: `Profil Pengadilan Agama Penajam`

- **Published At:** Schedule publication date/time
  - Leave empty for immediate publication

### Step 4: Add Content

You have two options:

#### Option A: Use Page Builder (Recommended)

1. Click **"Launch Page Builder"** button
2. The visual editor opens

**Add a Heading:**
1. Drag **"Heading"** from the left panel
2. Drop it on the canvas
3. In the right panel, enter text: `Tentang Pengadilan Agama Penajam`
4. Set level to `H1`

**Add a Paragraph:**
1. Drag **"Paragraph"** from the left panel
2. Drop it below the heading
3. Enter your text:
   ```
   Pengadilan Agama Penajam adalah lembaga peradilan
   di bawah Mahkamah Agung Republik Indonesia yang
   berwenang memeriksa, memutus, dan menyelesaikan
   perkara di tingkat pertama antara orang-orang yang
   beragama Islam.
   ```

**Preview Your Page:**
1. Click **"Preview"** button (top right)
2. See how it looks on desktop, tablet, and mobile
3. Click **"Save"** when satisfied

#### Option B: Use Manual JSON (Advanced)

For developers who prefer JSON structure.

### Step 5: Publish Your Page

1. Click **"Save"** button
2. Change **Status** to `Published`
3. Click **"Save"** again
4. Your page is now live at: `https://pa-penajam.go.id/tentang-kami`

**Congratulations!** You've created your first page.

---

## Managing News

### Creating a News Article

Let's publish a news announcement:

#### Step 1: Go to News Section

1. Click **"News"** in the sidebar
2. Click **"Create News"** button

#### Step 2: Enter News Details

**Required Fields:**

- **Title:** `Pengumuman: Libur Nasional`
  - Slug auto-generates as `pengumuman-libur-nasional`

- **Category:** Select `Berita` or `Pengumuman`

- **Content:** Write your article using the rich text editor

**Rich Text Editor Features:**
- **Bold** text: Select text and click **B**
- *Italic* text: Select text and click **I**
- Add headings: Use **H1**, **H2**, **H3** buttons
- Create lists: Click bulleted or numbered list icon
- Insert links: Highlight text, click link icon, enter URL
- Add images: Click image icon, upload or paste URL

**Optional Fields:**

- **Featured:** Check this to show on homepage
- **Published At:** Schedule publication (or leave empty for now)

#### Step 3: Publish News

1. Click **"Save"**
2. Change **Status** to `Published`
3. Click **"Save"** again
4. Your news is now live!

### Editing News

1. Go to **News** section
2. Find the article you want to edit
3. Click the article title or **"Edit"** button
4. Make your changes
5. Click **"Save"**

### Categorizing News

Before creating news articles, you should set up categories:

1. Go to **Categories** in sidebar
2. Click **"Create Category"**
3. Enter category name: `Pengumuman`
4. Select type: `news`
5. Click **"Save"**

Repeat for:
- `Berita` (News)
- `Artikel` (Articles)

---

## Uploading Documents

### Uploading a Document

Let's upload a PDF document:

#### Step 1: Go to Documents

1. Click **"Documents"** in the sidebar
2. Click **"Create Document"** button

#### Step 2: Enter Document Details

**Required Fields:**

- **Title:** `Surat Edaran No. 1/2026`
- **File:** Click **"Choose File"** and select your PDF
- **Category:** Select a category (e.g., `Surat Edaran`)

**Optional Fields:**

- **Description:** Brief description of the document
  - Example: `Tata cara pendaftaran perkara elektronik`

- **Is Public:** Check if document should be public
  - Uncheck if only for logged-in users

#### Step 3: Upload Document

1. Click **"Save"**
2. Wait for upload to complete
3. Document is now available for download

### Creating Document Categories

1. Go to **Categories** section
2. Create categories like:
   - `Surat Edaran` (Circular Letters)
   - `Putusan` (Court Decisions)
   - `Peraturan` (Regulations)
   - `Formulir` (Forms)
3. Select type: `document`
4. Click **"Save"**

### Managing Document Versions

If you upload a new version of an existing document:
1. Edit the existing document
2. Upload the new file
3. System automatically creates a new version
4. Old versions are archived

---

## Managing Menus

### Understanding Menu Locations

The website can have multiple menus:

- **Header Menu** - Top navigation bar
- **Footer Menu** - Links in footer
- **Sidebar Menu** - Side navigation (if used)
- **Mobile Menu** - Mobile-specific menu

### Creating a Menu

#### Step 1: Create Menu Structure

1. Click **"Menus"** in the sidebar
2. Click **"Create Menu"** button
3. Fill in:
   - **Name:** `Main Navigation`
   - **Location:** `header`
   - **Max Depth:** `3` (levels of nesting)
4. Click **"Save"**

#### Step 2: Add Menu Items

1. Click on your newly created menu
2. Click **"Add Item"** button
3. Fill in:
   - **Title:** `Beranda` (Home)
   - **URL Type:** `route`
   - **Route Name:** `home`
4. Click **"Save"**

Add more items:
- `Profil` (Profile)
- `Berita` (News)
- `Jadwal Sidang` (Court Schedules)
- `PPID` (Information Service)

#### Step 3: Organize Menu Items

1. Click **"Visual Editor"** tab
2. **Drag** items to reorder them
3. **Indent** items to create sub-menus (drag right)
4. Changes auto-save

**Example Menu Structure:**
```
Beranda
Profil
  ├── Sejarah
  ├── Visi Misi
  └── Struktur Organisasi
Berita
Jadwal Sidang
PPID
```

### Menu Item Types

**1. Route Links**
- Connects to application routes
- Example: `home`, `news.index`, `page.show`

**2. Page Links**
- Connects to pages you created
- Auto-updates if page slug changes

**3. Custom URLs**
- Manual URL entry
- Example: `/custom-path`

**4. External Links**
- Links to other websites
- Must include `https://`
- Opens in new tab by default

---

## Managing Court Schedules

### About Court Schedules

Court schedules are automatically synced from **SIPP** (Sistem Informasi Penelusuran Perkara). You don't need to manually enter them.

### Viewing Schedules

1. Click **"Court Schedules"** in sidebar
2. View all upcoming court hearings
3. Use filters to find specific schedules:
   - Filter by date range
   - Filter by judge
   - Filter by courtroom
   - Filter by case type

### Manual Sync

If you need to update schedules immediately:

1. Click **"Sync Now"** button
2. Wait for sync to complete
3. Progress bar shows sync status
4. View sync logs for details

### Sync Status

- **Last Sync:** Shows when data was last updated
- **Next Sync:** Shows when next automatic sync will occur
- **Status:** Success/Failure indicator

---

## Managing PPID Requests

### Understanding PPID

PPID (Pejabat Pengelola Informasi dan Dokumentasi) is the public information service required by law. Citizens can request information through this system.

### Viewing Requests

1. Click **"PPID Requests"** in sidebar
2. View all submitted requests
3. Each request shows:
   - **Requester Name** - Who submitted
   - **Request Type** - Category of request
   - **Description** - What they're asking for
   - **Status** - Current status
   - **Created At** - When submitted

### Request Workflow

Requests go through these stages:

1. **Pending** (Baru)
   - New request submitted
   - Awaiting your review

2. **Processed** (Diproses)
   - You're working on it
   - Gathering information

3. **Completed** (Selesai)
   - Response provided
   - Request closed

4. **Rejected** (Ditolak)
   - Cannot fulfill request
   - Reason provided

### Responding to Requests

1. Click on a request to view details
2. Review the request description
3. Type your response in the **Response** field
4. Attach any relevant documents
5. Change status to **Processed** or **Completed**
6. Click **"Save"**
7. System automatically emails the requester

---

## Common Tasks

### Duplicating a Page

If you want to create a similar page:

1. Go to **Pages** section
2. Find the page you want to duplicate
3. Click **"Actions"** → **"Duplicate"**
4. Edit the duplicate as needed
5. Save with new title and slug

### Bulk Publishing

Publish multiple items at once:

1. Go to **News** or **Documents** section
2. Select items using checkboxes
3. Choose **"Publish"** from bulk actions
4. Click **"Apply"**

### Changing Page Template

If you want to use a different template:

1. Edit the page
2. Choose a different template from the dropdown
3. Content from the page builder will adapt to the new template
4. Click **"Save"**

### Searching Content

Use the search bar to quickly find content:

1. Go to any section (Pages, News, Documents)
2. Use the search box at the top
3. Type your search term
4. Results appear instantly

### Filtering Content

Use filters to narrow down results:

- **By Status:** Draft vs Published
- **By Category:** Filter by category
- **By Date:** Filter by creation/publication date
- **By Author:** Filter by who created it

---

## Best Practices

### Content Creation

1. **Plan Your Content**
   - Outline before creating
   - Prepare images in advance
   - Know your categories

2. **Use Clear Titles**
   - Descriptive and concise
   - Include keywords
   - Under 60 characters

3. **Optimize Images**
   - Compress before uploading (under 500KB)
   - Use JPG for photos, PNG for graphics
   - Add descriptive alt text

4. **Preview Before Publishing**
   - Always check preview
   - Test on mobile view
   - Verify all links work

5. **Save Your Work**
   - Save frequently
   - Use Draft status for work in progress
   - Publish only when ready

### Organization

1. **Use Categories Effectively**
   - Create logical category structure
   - Don't create too many (aim for 5-10)
   - Keep naming consistent

2. **Maintain Menu Structure**
   - Keep menus simple (max 3 levels deep)
   - Use clear, short labels
   - Group related items together

3. **Regular Maintenance**
   - Review and update old content
   - Archive outdated items
   - Check for broken links

### Security

1. **Strong Passwords**
   - Use complex passwords
   - Change regularly (every 3 months)
   - Don't share with others

2. **Log Out When Done**
   - Always log out after sessions
   - Don't save passwords in shared browsers
   - Use private mode on shared devices

3. **Report Issues**
   - Report bugs immediately
   - Notify admin of suspicious activity
   - Don't ignore error messages

---

## Troubleshooting

### Common Issues

#### Page Builder Won't Load

**Try these:**
1. Clear browser cache (Ctrl+F5)
2. Try a different browser
3. Check your internet connection
4. Contact technical support

#### Can't Publish Content

**Possible reasons:**
1. Your role doesn't have publish permission
2. Required fields are missing
3. Validation errors present

**Solutions:**
1. Save as Draft instead
2. Contact admin for approval
3. Fill in all required fields (marked with *)

#### Images Not Uploading

**Check:**
1. File size (must be under 5MB)
2. File type (JPG, PNG, GIF, WEBP only)
3. Internet connection
4. Browser permissions

**Solution:**
1. Compress image before uploading
2. Convert to supported format
3. Try a different browser

#### Changes Not Visible on Website

**Reasons:**
1. Browser cache showing old version
2. Content not published
3. Scheduled for future date

**Solutions:**
1. Hard refresh (Ctrl+Shift+R)
2. Check content status is "Published"
3. Verify Published At date is in the past

### Getting Help

If you're stuck:

1. **Check the Admin Documentation**
   - Comprehensive guide for all features
   - Searchable by topic

2. **Watch Training Videos**
   - Video tutorials for common tasks
   - Step-by-step walkthroughs

3. **Contact Support**
   - Email: tech-support@pa-penajam.go.id
   - Phone: [Insert phone number]
   - Hours: Monday-Friday, 8:00-16:00 WIB

4. **Ask a Colleague**
   - More experienced users
   - Admin team members

---

## Keyboard Shortcuts

Save time with these shortcuts:

- **Ctrl + S** - Save current form
- **Ctrl + Z** - Undo (in text editor)
- **Ctrl + Y** - Redo (in text editor)
- **Ctrl + B** - Bold (in text editor)
- **Ctrl + I** - Italic (in text editor)
- **Esc** - Close modal/dialog
- **Enter** - Submit form

---

## Next Steps

### Recommended Learning Path

**Week 1: Basics**
- [ ] Complete your profile
- [ ] Create your first page
- [ ] Publish your first news article
- [ ] Upload a document

**Week 2: Intermediate**
- [ ] Use Page Builder for complex layouts
- [ ] Create and manage categories
- [ ] Set up menus
- [ ] Respond to PPID requests

**Week 3: Advanced**
- [ ] Create page templates
- [ ] Use visual menu editor
- [ ] Manage court schedules
- [ ] Generate reports

### Additional Resources

- **[Admin Documentation](../admin/README.md)** - Complete guide
- **[Technical Documentation](../technical/ARCHITECTURE.md)** - For developers
- **[Video Tutorials]** - Coming soon
- **[FAQ](FAQ.md)** - Frequently asked questions

### Training Sessions

Monthly training sessions are available:
- **Basic Training** - First Monday of each month
- **Advanced Training** - Third Monday of each month
- **Q&A Session** - Last Friday of each month

**To register:** Email training@pa-penajam.go.id

---

## Glossary

| Term | Meaning |
|------|---------|
| **Slug** | URL-friendly version of a title (e.g., "tentang-kami") |
| **Page Builder** | Visual drag-and-drop page editor |
| **Rich Text Editor** | WYSIWYG text editor for formatting |
| **SIPP** | Sistem Informasi Penelusuran Perkara (Case Tracking System) |
| **PPID** | Pejabat Pengelola Informasi dan Dokumentasi (Information Service) |
| **Draft** | Unpublished content |
| **Published** | Live content visible to public |
| **Category** | Organizational grouping for content |
| **Menu** | Website navigation structure |

---

## Checklist

Before you start managing content independently, make sure you:

- [ ] Successfully logged in and completed profile
- [ ] Created a test page and published it
- [ ] Created and published a news article
- [ ] Uploaded a document
- [ ] Created a menu and added menu items
- [ ] Used the Page Builder
- [ ] Responded to a PPID request
- [ ] Used filters to find content
- [ ] Bulk edited content
- [ ] Previewed content on mobile view

**Congratulations!** You're now ready to manage the PA Penajam website.

---

**Need help?** Contact us at tech-support@pa-penajam.go.id

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
