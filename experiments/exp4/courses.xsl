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