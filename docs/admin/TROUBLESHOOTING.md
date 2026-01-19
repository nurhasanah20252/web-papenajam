# PA Penajam Website - Troubleshooting Guide

## Overview

This guide helps you diagnose and resolve common issues with the PA Penajam website admin panel and frontend.

---

## Table of Contents

1. [Quick Diagnostics](#quick-diagnostics)
2. [Login & Access Issues](#login--access-issues)
3. [Content Management Issues](#content-management-issues)
4. [Page Builder Issues](#page-builder-issues)
5. [Media & File Issues](#media--file-issues)
6. [SIPP Integration Issues](#sipp-integration-issues)
7. [Performance Issues](#performance-issues)
8. [Error Messages](#error-messages)
9. [Getting Help](#getting-help)

---

## Quick Diagnostics

### Before Troubleshooting

Run through this checklist:

- [ ] Can you access other websites? (Internet connection)
- [ ] Are you using a supported browser? (Chrome, Firefox, Safari, Edge)
- [ ] Have you tried clearing your browser cache?
- [ ] Have you tried a different browser?
- [ ] Are you logged in with the correct account?
- [ ] Do other users have the same issue?

### Browser Cache Clear

**Chrome/Edge:**
1. Press `Ctrl + Shift + Delete`
2. Select "Cached images and files"
3. Click "Clear data"

**Firefox:**
1. Press `Ctrl + Shift + Delete`
2. Select "Cache"
3. Click "Clear Now"

**Safari:**
1. Press `Cmd + Option + E`
2. Or Develop → Empty Caches

---

## Login & Access Issues

### Issue: Cannot Login

**Symptoms:**
- Login page shows error
- "Invalid credentials" message
- Page redirects to login

**Possible Causes:**
1. Wrong username/password
2. Account locked
3. Browser cache issue
4. System maintenance

**Solutions:**

**1. Verify Credentials**
- Check email for correct spelling
- Reset password if forgotten
- Try copy-pasting credentials

**2. Reset Password**
```
1. Click "Forgot Password" on login page
2. Enter your email address
3. Check email for reset link
4. Create new password
5. Login with new password
```

**3. Check Account Status**
- Contact admin to verify account is active
- Confirm your role hasn't changed
- Check if account is locked

**4. Clear Browser Cache**
- See "Quick Diagnostics" above

**5. Try Different Browser**
- Test in Chrome, Firefox, or Edge
- If works in one browser, clear cache in original

### Issue: "Access Denied" or "Unauthorized"

**Symptoms:**
- Can login but can't access certain features
- "You don't have permission" message
- Some menu items missing

**Possible Causes:**
1. Insufficient role permissions
2. Content requires approval
3. Feature not enabled for your role

**Solutions:**

**1. Check Your Role**
```
1. Click your name (top right)
2. Check your role displayed
3. Review role permissions
```

**2. Contact Administrator**
- Request permission if needed
- Ask for role change if appropriate
- Verify account is properly configured

**3. Use Draft Mode**
- If you can't publish, save as Draft
- Request approval from admin

### Issue: Session Keeps Expiring

**Symptoms:**
- Logged out frequently
- Have to re-login constantly

**Solutions:**

**1. Check Session Timeout**
- Default session: 2 hours
- Contact admin if too short

**2. Stable Internet Connection**
- Unstable connections cause session issues
- Try wired connection instead of WiFi

**3. Browser Settings**
- Disable private/incognito mode
- Allow cookies for the domain
- Check browser extensions

---

## Content Management Issues

### Issue: Cannot Create/Edit Page

**Symptoms:**
- "Create Page" button missing
- Edit button not working
- Page Builder won't open

**Possible Causes:**
1. Insufficient permissions
2. Browser JavaScript disabled
3. Page locked by another user

**Solutions:**

**1. Check Permissions**
- Verify your role allows page editing
- Contact admin if permissions missing

**2. Enable JavaScript**
```
Chrome:
1. Settings → Privacy and security → Site settings
2. JavaScript → Allow

Firefox:
1. Options → Privacy & Security
2. Permissions → Enable JavaScript
```

**3. Check for Page Lock**
- Another user might be editing
- Wait a few minutes and try again
- Contact the other user

### Issue: Changes Not Visible on Website

**Symptoms:**
- Edited content doesn't show
- Still see old version
- Published but not live

**Possible Causes:**
1. Browser cache showing old version
2. Content not actually published
3. Scheduled for future date
4. CDN cache not cleared

**Solutions:**

**1. Hard Refresh Browser**
```
Windows: Ctrl + Shift + R
Mac: Cmd + Shift + R
```

**2. Verify Publication Status**
```
1. Go to content list
2. Check "Status" column
3. Must be "Published" (not "Draft" or "Scheduled")
```

**3. Check Published Date**
```
1. Edit the content
2. Check "Published At" field
3. Must be in the past (not future)
4. Update if needed
```

**4. Clear Application Cache**
- Contact technical support
- They will clear server-side cache

### Issue: Cannot Delete Content

**Symptoms:**
- Delete button missing
- "Cannot delete" error
- Content still appears after deletion

**Possible Causes:**
1. Insufficient permissions
2. Content has dependencies
3. Soft delete in effect

**Solutions:**

**1. Check Permissions**
- Only admins can delete certain content
- Contact admin for deletion

**2. Remove Dependencies**
```
1. Check if content is linked elsewhere
2. Remove from menus
3. Remove from categories
4. Try deletion again
```

**3. Soft Delete**
- Content might be "soft deleted" (hidden, not removed)
- Contact admin to permanently remove

---

## Page Builder Issues

### Issue: Page Builder Won't Load

**Symptoms:**
- Blank screen when opening
- Loading spinner never stops
- Browser console errors

**Possible Causes:**
1. JavaScript error
2. Network connectivity issue
3. Browser compatibility

**Solutions:**

**1. Check Browser Console**
```
Chrome/Edge:
1. Press F12
2. Click Console tab
3. Look for red errors
4. Screenshot errors for support
```

**2. Clear Browser Cache**
- See "Quick Diagnostics" above

**3. Try Different Browser**
- Test in Chrome if using Firefox
- Helps identify browser-specific issues

**4. Check Internet Connection**
- Unstable connection causes issues
- Try wired connection

**5. Disable Browser Extensions**
- Ad blockers sometimes interfere
- Try incognito/private mode

### Issue: Components Not Working

**Symptoms:**
- Can't add components
- Components disappear
- Settings panel doesn't open

**Solutions:**

**1. Reload Page Builder**
- Click "Reload" button
- Or press F5

**2. Check Component Limits**
- Some components have limits (e.g., max 6 columns)
- Don't exceed component limits

**3. Clear Browser Cache**
- Cached files might be outdated

**4. Try Different Component**
- Test if other components work
- Helps identify if issue is component-specific

### Issue: Cannot Save Page

**Symptoms:**
- Save button not working
- "Error saving" message
- Changes lost after refresh

**Possible Causes:**
1. Validation error
2. Network timeout
3. Server error

**Solutions:**

**1. Check Required Fields**
```
1. Title is required
2. Slug must be unique
3. Fill in all required fields (marked with *)
```

**2. Check Slug Uniqueness**
```
1. Edit the slug
2. Make it unique
3. Try saving again
```

**3. Try Smaller Changes**
- Save more frequently
- Large changes might timeout
- Break into smaller saves

**4. Check Network**
- Slow connection causes timeouts
- Try again with better connection

---

## Media & File Issues

### Issue: Image Won't Upload

**Symptoms:**
- Upload fails
- "File too large" error
- "Invalid file type" error

**Possible Causes:**
1. File too large
2. Unsupported file type
3. Network timeout

**Solutions:**

**1. Check File Size**
- Maximum: 5MB for images
- Compress image before uploading
- Use online compression tools

**2. Check File Type**
```
Supported: JPG, PNG, GIF, WEBP, SVG
Not supported: BMP, TIFF, PSD (convert first)
```

**3. Check File Name**
- Avoid special characters
- Use only letters, numbers, hyphens
- Example: `my-image.jpg` not `my image!.jpg`

**4. Try Different Browser**
- Some browsers have issues with certain files

### Issue: Document Won't Upload

**Symptoms:**
- Upload fails immediately
- Progress bar stops
- "Upload error" message

**Possible Causes:**
1. File too large (max 20MB)
2. Unsupported format
3. Corrupted file

**Solutions:**

**1. Check File Size**
- Maximum: 20MB for documents
- Compress if possible
- Split large files

**2. Check File Type**
```
Supported: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX
Not supported: EXE, ZIP, RAR (security restriction)
```

**3. Test File Locally**
- Can you open the file?
- File might be corrupted
- Try resaving the file

**4. Check Storage Space**
- Contact support if storage full
- Delete old files if needed

### Issue: Images Not Displaying

**Symptoms:**
- Broken image icons
- Images show as boxes
- Very slow loading

**Solutions:**

**1. Check Image Path**
- File might have been moved
- Re-upload the image

**2. Clear Browser Cache**
- Old cached version might be broken
- Hard refresh (Ctrl + Shift + R)

**3. Check Image Permissions**
- Image might not be public
- Contact admin to verify

**4. Slow Connection**
- Large images load slowly
- Optimize images before uploading

---

## SIPP Integration Issues

### Issue: Court Schedules Not Syncing

**Symptoms:**
- Last sync time outdated
- "Sync failed" status
- No new schedules appearing

**Possible Causes:**
1. SIPP API down
2. API credentials invalid
3. Network connectivity issue

**Solutions:**

**1. Check Sync Status**
```
1. Go to Court Schedules
2. Check "Last Sync" time
3. Check "Sync Status" indicator
```

**2. Check Sync Logs**
```
1. Click "Sync Logs" button
2. Look for error messages
3. Note error details for support
```

**3. Manual Sync**
```
1. Click "Sync Now" button
2. Wait for completion
3. Check for errors
```

**4. Verify API Credentials**
- Contact technical support
- Credentials in Settings may be incorrect
- SIPP API might be down

### Issue: Missing Schedules

**Symptoms:**
- Expected schedules not showing
- Gaps in schedule data
- Old schedules only

**Possible Causes:**
1. Date range filters
2. Sync incomplete
3. Data not in SIPP yet

**Solutions:**

**1. Check Date Filters**
```
1. Review filter settings
2. Expand date range
3. Clear filters
```

**2. Wait for SIPP Update**
- SIPP system might not have data yet
- Check SIPP directly
- Contact SIPP administrator

**3. Full Sync**
```
1. Contact technical support
2. Request full database sync
3. May take several hours
```

---

## Performance Issues

### Issue: Admin Panel Slow

**Symptoms:**
- Pages load slowly
- Lag when typing
- Delayed button responses

**Possible Causes:**
1. Large amount of data
2. Server load
3. Network issues

**Solutions:**

**1. Use Filters**
- Filter content to reduce data
- Use search to find specific items
- Avoid loading all records

**2. Close Unused Tabs**
- Multiple tabs use memory
- Close tabs you don't need

**3. Clear Browser Cache**
- Old cached files slow things down
- See "Quick Diagnostics"

**4. Check Internet Speed**
- Test at speedtest.net
- Try wired connection
- Contact IT if slow

### Issue: Website Slow for Visitors

**Symptoms:**
- Visitors complain about slowness
- High page load times
- Slow image loading

**Solutions:**

**1. Optimize Images**
- Compress before uploading
- Use appropriate dimensions
- Use WebP format when possible

**2. Reduce Page Complexity**
- Don't overload Page Builder
- Limit number of components
- Lazy load images

**3. Check Server Resources**
- Contact technical support
- Server might be overloaded
- May need upgrade

**4. Enable Caching**
- Contact technical support
- Caching dramatically improves speed

---

## Error Messages

### "404 Page Not Found"

**Meaning:** Page or resource doesn't exist

**Solutions:**
1. Check URL for typos
2. Page might have been deleted
3. Clear browser cache
4. Contact support if issue persists

### "500 Server Error"

**Meaning:** Server-side error

**Solutions:**
1. Try again in a few minutes
2. Clear browser cache
3. Contact technical support
4. Check if system maintenance scheduled

### "503 Service Unavailable"

**Meaning:** Server overloaded or maintenance

**Solutions:**
1. Wait a few minutes
2. Check maintenance announcements
3. Contact support if persists >30 minutes

### "422 Unprocessable Entity"

**Meaning:** Validation error

**Solutions:**
1. Check form for error messages (red text)
2. Fill in required fields
3. Fix validation errors
4. Try again

### "504 Gateway Timeout"

**Meaning:** Server took too long to respond

**Solutions:**
1. Try again
2. Reduce data amount (fewer items)
3. Check if server is overloaded
4. Contact support if frequent

### "Connection Timed Out"

**Meaning:** Network communication failed

**Solutions:**
1. Check internet connection
2. Try different network
3. Clear browser cache
4. Try again later

### "OutOfMemoryException"

**Meaning:** Server ran out of memory

**Solutions:**
1. Contact technical support immediately
2. Server needs more resources
3. May need to restart services

---

## Getting Help

### Self-Service Resources

1. **Admin Documentation**
   - [Comprehensive Guide](README.md)
   - Search by topic
   - Step-by-step instructions

2. **Training Materials**
   - Video tutorials
   - Hands-on exercises
   - Assessment quizzes

3. **FAQ Section**
   - Common questions
   - Quick answers
   - Best practices

### When to Contact Support

**Contact support for:**
- System-wide issues (all users affected)
- Security concerns
- Data loss
- Features not working as expected
- Error messages not in this guide

**Don't contact for:**
- How-to questions (check docs first)
- Feature requests (use request form)
- Browser issues (try different browser)

### Contact Information

**Technical Support:**
- **Email:** tech-support@pa-penajam.go.id
- **Phone:** [Insert phone number]
- **Hours:** Monday-Friday, 8:00-16:00 WIB
- **Response Time:** Within 4 hours

**Content Help:**
- **Email:** content@pa-penajam.go.id
- **Hours:** Monday-Friday, 8:00-16:00 WIB

**Emergency (System Down):**
- **Phone:** [Insert emergency number]
- **24/7 Availability**

### Reporting Issues

When reporting an issue, include:

1. **Description:** What were you trying to do?
2. **Steps:** What steps did you take?
3. **Expected:** What did you expect to happen?
4. **Actual:** What actually happened?
5. **Error:** Exact error message (if any)
6. **Browser:** Which browser and version?
7. **Screenshot:** If applicable

**Example Report:**
```
Subject: Cannot create new page

Description:
I was trying to create a new page titled "About Us"

Steps:
1. Clicked "Pages" in sidebar
2. Clicked "Create Page" button
3. Filled in title: "About Us"
4. Clicked "Save" button

Expected:
Page should be created

Actual:
Got error message: "500 Server Error"

Error:
"500 Server Error" displayed

Browser:
Chrome 120.0.6099.109

Screenshot:
[Attach screenshot]
```

### Escalation Path

1. **Level 1: Self-Service**
   - Check documentation
   - Review training materials
   - Try solutions in this guide

2. **Level 2: Content Team**
   - Contact content@pa-penajam.go.id
   - For content-related issues
   - Response within 24 hours

3. **Level 3: Technical Support**
   - Contact tech-support@pa-penajam.go.id
   - For technical issues
   - Response within 4 hours

4. **Level 4: Emergency**
   - Call emergency number
   - For system-wide outages
   - Immediate response

---

## Prevention Tips

### Regular Maintenance

**Weekly:**
- Clear browser cache
- Review and update old content
- Check for broken links

**Monthly:**
- Change password (security)
- Review user permissions
- Clean up unused media

**Quarterly:**
- Comprehensive content review
- User training refresher
- Performance check

### Best Practices

1. **Save Frequently**
   - Don't wait until finished
   - Save every 5-10 minutes
   - Prevents data loss

2. **Use Draft Mode**
   - Save as draft while working
   - Publish only when ready
   - Prevents premature publication

3. **Test Changes**
   - Always preview before publishing
   - Test on mobile devices
   - Check all links

4. **Backup Important Work**
   - Export important pages
   - Keep local copies of documents
   - Note your page URLs

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
**Maintained By:** Technical Support Team
