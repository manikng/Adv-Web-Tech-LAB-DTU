# Experiment 4 — XML Data Representation and Display using XSLT

**Student Name:** Manish Singh  
**Roll No:** 23/CS/244  
**Course:** Advanced Web Technology  
**Semester:** 7th Semester  
**Date:** September 28, 2026

---

## Aim

To create an XML document representing a Course Catalogue with at least 5 course records, and an XSLT stylesheet to transform the XML data into a well-formatted HTML table with CSS styling, demonstrating XML-to-HTML transformation in a web browser.

---

## Objective

1. Create a well-formed XML document (`courses.xml`) containing course catalogue data with at least 5 course records
2. Store appropriate information for each course: Course ID, Course Name, Course Code, Faculty/Instructor, Credits, Course Type
3. Create an XSLT stylesheet (`courses.xsl`) to transform XML data into structured HTML
4. Link the XSLT stylesheet to the XML document using XML processing instruction
5. Use XSLT to generate a properly structured HTML table with all course details
6. Apply CSS formatting within the XSLT for readable, organized display
7. Use XSLT elements: `xsl:for-each` for iteration, `xsl:value-of` for data display, `xsl:choose` for conditional formatting
8. Verify XML is well-formed and successfully processed by XSLT
9. Test dynamic updates by modifying XML data and verifying browser output updates

---

## Procedure / Algorithm

### Step 1: Create Project Structure
- Create `experiments/exp4/` directory
- Create `courses.xml` and `courses.xsl` in this directory

### Step 2: Design XML Structure
Define hierarchical structure:
```
CourseCatalogue (root)
  └── Course (repeating, 6 records)
       ├── CourseID
       ├── CourseName
       ├── CourseCode
       ├── Faculty
       ├── Credits
       └── CourseType (Core/Elective)
```

### Step 3: Create XML Document (courses.xml)
- Add XML declaration: `<?xml version="1.0" encoding="UTF-8"?>`
- Add XSLT processing instruction: `<?xml-stylesheet type="text/xsl" href="courses.xsl"?>`
- Create root element `<CourseCatalogue>`
- Add 6 `<Course>` records with all required fields
- Ensure well-formedness: proper nesting, closing tags, quoted attributes

### Step 4: Create XSLT Stylesheet (courses.xsl)
- Add XSLT declaration with namespace: `xmlns:xsl="http://www.w3.org/1999/XSL/Transform"`
- Set output method to HTML with indentation
- Create main template matching root (`match="/"`)
- Generate complete HTML document structure with embedded CSS
- Use `<xsl:for-each select="CourseCatalogue/Course">` to iterate courses
- Use `<xsl:value-of select="ElementName"/>` to extract data
- Use `<xsl:choose>` with `<xsl:when>` for conditional badge styling (Core vs Elective)
- Use `<xsl:value-of select="count(CourseCatalogue/Course)"/>` for dynamic course count

### Step 5: Embed CSS Styling in XSLT
Include modern styling:
- Gradient background, card container with shadow
- Styled table with header gradient, alternating rows, hover effects
- Conditional badges: Blue for Core, Amber for Elective
- Monospace fonts for IDs and codes
- Responsive design with max-width container

### Step 6: Test Transformation
- Open `courses.xml` in web browser (Chrome/Edge/Firefox)
- Browser automatically applies XSLT and renders HTML table
- Verify all 6 courses display correctly with proper formatting

### Step 7: Test Dynamic Updates
- Edit `courses.xml` (add/modify/delete course records)
- Save file and refresh browser
- Verify table updates automatically without XSLT changes

### Step 8: Capture Screenshots
- Browser rendering of course catalogue
- XML source view
- Modified XML with new course and updated browser output

---

## XML Code Used (courses.xml)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="courses.xsl"?>
<CourseCatalogue>
    <Course>
        <CourseID>CSE101</CourseID>
        <CourseName>Introduction to Computer Science</CourseName>
        <CourseCode>CS101</CourseCode>
        <Faculty>Dr. Sarah Johnson</Faculty>
        <Credits>4</Credits>
        <CourseType>Core</CourseType>
    </Course>
    <Course>
        <CourseID>CSE201</CourseID>
        <CourseName>Data Structures and Algorithms</CourseName>
        <CourseCode>CS201</CourseCode>
        <Faculty>Prof. Michael Chen</Faculty>
        <Credits>4</Credits>
        <CourseType>Core</CourseType>
    </Course>
    <Course>
        <CourseID>CSE301</CourseID>
        <CourseName>Database Management Systems</CourseName>
        <CourseCode>CS301</CourseCode>
        <Faculty>Dr. Emily Rodriguez</Faculty>
        <Credits>3</Credits>
        <CourseType>Core</CourseType>
    </Course>
    <Course>
        <CourseID>CSE401</CourseID>
        <CourseName>Machine Learning Fundamentals</CourseName>
        <CourseCode>CS401</CourseCode>
        <Faculty>Prof. David Kim</Faculty>
        <Credits>3</Credits>
        <CourseType>Elective</CourseType>
    </Course>
    <Course>
        <CourseID>CSE402</CourseID>
        <CourseName>Web Development Technologies</CourseName>
        <CourseCode>CS402</CourseCode>
        <Faculty>Ms. Lisa Anderson</Faculty>
        <Credits>3</Credits>
        <CourseType>Elective</CourseType>
    </Course>
    <Course>
        <CourseID>CSE403</CourseID>
        <CourseName>Cybersecurity Essentials</CourseName>
        <CourseCode>CS403</CourseCode>
        <Faculty>Dr. James Wilson</Faculty>
        <Credits>3</Credits>
        <CourseType>Elective</CourseType>
    </Course>
</CourseCatalogue>
```
---

## XSLT Code Used (courses.xsl) - Part 1: Stylesheet Declaration & CSS

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    
    <xsl:template match="/">
        <html>
            <head>
                <title>Course Catalogue</title>
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        margin: 0;
                        padding: 20px;
                    }
                    .container {
                        max-width: 1000px;
                        margin: 0 auto;
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                        overflow: hidden;
                    }
                    h1 {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        margin: 0;
                        padding: 30px;
                        text-align: center;
                        font-size: 2.5rem;
                        font-weight: 600;
                    }
                    .subtitle {
                        text-align: center;
                        color: #666;
                        padding: 10px 0 20px;
                        font-size: 1.1rem;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 0 20px 20px;
                    }
                    th {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 15px 12px;
                        text-align: left;
                        font-weight: 600;
                        font-size: 0.95rem;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
```
td {
                        padding: 14px 12px;
                        border-bottom: 1px solid #eee;
                        font-size: 0.95rem;
                    }
                    tr:nth-child(even) {
                        background-color: #f8f9fa;
                    }
                    tr:hover {
                        background-color: #eef2ff;
                        transition: background-color 0.2s ease;
                    }
                    .course-type-core {
                        background: #dbeafe;
                        color: #1e40af;
                        padding: 4px 10px;
                        border-radius: 20px;
                        font-size: 0.8rem;
                        font-weight: 600;
                        display: inline-block;
                        text-transform: uppercase;
                    }
                    .course-type-elective {
                        background: #fef3c7;
                        color: #92400e;
                        padding: 4px 10px;
                        border-radius: 20px;
                        font-size: 0.8rem;
                        font-weight: 600;
                        display: inline-block;
                        text-transform: uppercase;
                    }
                    .credits {
                        text-align: center;
                        font-weight: 600;
                        color: #374151;
                    }
                    .course-id {
                        font-family: 'Monospace', monospace;
                        font-weight: 600;
                        color: #667eea;
                    }
                    .course-name {
                        font-weight: 500;
                        color: #1f2937;
                    }
                    .faculty {
                        color: #4b5563;
                    }
                    .course-code {
                        font-family: 'Monospace', monospace;
                        background: #f3f4f6;
                        padding: 2px 6px;
                        border-radius: 4px;
                        font-size: 0.85rem;
                    }
                    .footer {
                        text-align: center;
                        padding: 20px;
                        color: #9ca3af;
                        font-size: 0.85rem;
                        border-top: 1px solid #eee;
                        margin: 0 20px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>📚 Course Catalogue</h1>
                    <p class="subtitle">Computer Science Department - Academic Year 2024-2025</p>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Course ID</th>
                                <th>Course Name</th>
                                <th>Course Code</th>
                                <th>Faculty/Instructor</th>
                                <th>Credits</th>
                                <th>Course Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="CourseCatalogue/Course">
                                <tr>
                                    <td class="course-id"><xsl:value-of select="CourseID"/></td>
                                    <td class="course-name"><xsl:value-of select="CourseName"/></td>
                                    <td class="course-code"><xsl:value-of select="CourseCode"/></td>
                                    <td class="faculty"><xsl:value-of select="Faculty"/></td>
                                    <td class="credits"><xsl:value-of select="Credits"/></td>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="CourseType = 'Core'">
                                                <span class="course-type-core">Core</span>
                                            </xsl:when>
                                            <xsl:when test="CourseType = 'Elective'">
                                                <span class="course-type-elective">Elective</span>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <span><xsl:value-of select="CourseType"/></span>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                    
                    <div class="footer">
                        Total Courses: <xsl:value-of select="count(CourseCatalogue/Course)"/> | 
                        Last Updated: 2024-2025
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
```
---

## CSS Code Used (Embedded in XSLT)

The CSS is embedded within the `<style>` tag in the XSLT stylesheet. Key styling features:

| CSS Class/Element | Purpose |
|-------------------|---------|
| `body` | Gradient background, full viewport height |
| `.container` | White card with shadow, max-width 1000px, rounded corners |
| `h1` | Gradient header with emoji, centered |
| `table` | Full-width, collapsed borders |
| `th` | Gradient header row, uppercase, letter-spacing |
| `tr:nth-child(even)` | Alternating row colors (zebra striping) |
| `tr:hover` | Highlight row on hover with smooth transition |
| `.course-type-core` | Blue badge for Core courses |
| `.course-type-elective` | Amber badge for Elective courses |
| `.course-id` | Monospace, purple color for Course IDs |
| `.course-code` | Monospace, gray background for Course Codes |
| `.credits` | Centered, bold credits column |
| `.footer` | Dynamic course count, centered |

---

## Screenshots

### 1. XML/XSLT Output in Browser
![Course Catalogue Output](screenshots/exp4-browser-output.png)
*Shows the rendered HTML table with 6 courses, gradient header, styled badges, and footer with dynamic count*

### 2. Course Data Displayed in Browser
![Course Data](screenshots/exp4-course-data.png)
*Close-up of table rows showing all fields: Course ID, Name, Code, Faculty, Credits, Type with conditional badges*

### 3. XML Data Changes Reflected in Output
![XML Changes](screenshots/exp4-xml-changes.png)
*Shows modified courses.xml with added 7th course (CSE404 - Quantum Computing) and browser refreshed showing new row*

---

## Result

The experiment was successfully completed. The XML/XSLT Course Catalogue demonstrates:

✅ **Well-formed XML document** (`courses.xml`) with proper hierarchy and 6 course records (exceeds minimum 5)  
✅ **All required fields stored**: Course ID, Course Name, Course Code, Faculty, Credits, Course Type  
✅ **XSLT processing instruction** linking XML to stylesheet: `<?xml-stylesheet type="text/xsl" href="courses.xsl"?>`  
✅ **XSLT transformation** generating complete HTML document with embedded CSS  
✅ **HTML table structure** with thead, tbody, proper semantic markup  
✅ **XSLT elements used**: `xsl:for-each` (iteration), `xsl:value-of` (data extraction), `xsl:choose/when/otherwise` (conditional formatting)  
✅ **Conditional formatting**: Core courses show blue badge, Elective courses show amber badge  
✅ **Dynamic content**: Course count in footer updates automatically via `count()` function  
✅ **Modern CSS styling**: Gradients, shadows, hover effects, responsive card layout, badge system  
✅ **Browser transformation**: Opening `courses.xml` directly renders styled table  
✅ **Dynamic updates verified**: Adding/modifying XML data instantly reflects in browser on refresh  
✅ **No external libraries** — pure XML, XSLT, and CSS  
✅ **Data separation**: All data in XML, presentation in XSLT, styling in embedded CSS  

**File Location:** `D:\7-sem\advWebTechRepo\experiments\exp4\`  
**Files:** `courses.xml`, `courses.xsl`