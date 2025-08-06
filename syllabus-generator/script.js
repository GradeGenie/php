// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication status first
    if (window.authManager) {
        window.authManager.checkAuthStatus().then(isAuthenticated => {
            if (!isAuthenticated) {
                // User not authenticated or trial ended, auth modal will be shown by authManager
                console.log('User not authenticated or trial ended');
            }
        });
    }
    
    // Get references to DOM elements
    const syllabusForm = document.getElementById('syllabusForm');
    const syllabusPreview = document.getElementById('syllabusPreview');
    const syllabusContent = document.getElementById('syllabusContent');
    const downloadBtn = document.getElementById('downloadBtn');
    const backToEditBtn = document.getElementById('backToEditBtn');
    const addWeekBtn = document.getElementById('addWeekBtn');
    const addGradingBtn = document.getElementById('addGradingBtn');
    const scheduleContainer = document.getElementById('scheduleContainer');
    const gradingContainer = document.getElementById('gradingContainer');
    
    // Add user account UI elements
    const headerElement = document.querySelector('header');
    const userAccountDiv = document.createElement('div');
    userAccountDiv.className = 'user-account';
    userAccountDiv.innerHTML = `
        <button id="login-signup-btn" class="btn-secondary">Login / Sign Up</button>
        <div id="user-info" class="hidden">
            <span id="user-name"></span>
            <button id="logout-btn" class="btn-secondary">Logout</button>
        </div>
    `;
    headerElement.appendChild(userAccountDiv);
    
    // Setup user account UI event listeners
    document.getElementById('login-signup-btn').addEventListener('click', function() {
        if (window.authManager) {
            window.authManager.showAuthModal();
        }
    });
    
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (window.authManager) {
            window.authManager.logoutUser();
            updateUserUI(null);
        }
    });
    
    // Update user UI based on authentication state
    function updateUserUI(user) {
        const loginSignupBtn = document.getElementById('login-signup-btn');
        const userInfo = document.getElementById('user-info');
        const userName = document.getElementById('user-name');
        
        if (user) {
            loginSignupBtn.classList.add('hidden');
            userInfo.classList.remove('hidden');
            userName.textContent = user.name || user.email;
        } else {
            loginSignupBtn.classList.remove('hidden');
            userInfo.classList.add('hidden');
            userName.textContent = '';
        }
    }
    
    // Check for user on page load
    if (window.authManager) {
        const currentUser = window.authManager.getCurrentUser();
        updateUserUI(currentUser);
    }
    
    // Counter for dynamic elements
    let weekCount = 1;
    let gradingCount = 1;
    
    // Add event listeners
    syllabusForm.addEventListener('submit', function(e) {
        // Check authentication before generating syllabus
        if (window.authManager) {
            window.authManager.checkAuthStatus().then(isAuthenticated => {
                if (isAuthenticated) {
                    generateSyllabus(e);
                }
            });
        } else {
            generateSyllabus(e);
        }
    });
    
    downloadBtn.addEventListener('click', function() {
        // Check authentication before downloading PDF
        if (window.authManager) {
            window.authManager.checkAuthStatus().then(isAuthenticated => {
                if (isAuthenticated) {
                    downloadPDF();
                }
            });
        } else {
            downloadPDF();
        }
    });
    
    backToEditBtn.addEventListener('click', backToEdit);
    addWeekBtn.addEventListener('click', addWeekItem);
    addGradingBtn.addEventListener('click', addGradingItem);
    
    // Function to add a new week item to the schedule
    function addWeekItem() {
        weekCount++;
        const weekItem = document.createElement('div');
        weekItem.className = 'schedule-item';
        weekItem.innerHTML = `
            <div class="form-group">
                <label for="week${weekCount}">Week ${weekCount}</label>
                <input type="text" id="week${weekCount}" name="week${weekCount}" placeholder="Topic">
            </div>
            <div class="form-group">
                <label for="week${weekCount}Description">Description</label>
                <textarea id="week${weekCount}Description" name="week${weekCount}Description" rows="2"></textarea>
            </div>
            <button type="button" class="btn-secondary remove-btn" onclick="this.parentElement.remove()">Remove</button>
        `;
        scheduleContainer.appendChild(weekItem);
    }
    
    // Function to add a new grading component
    function addGradingItem() {
        gradingCount++;
        const gradingItem = document.createElement('div');
        gradingItem.className = 'grading-item';
        gradingItem.innerHTML = `
            <div class="form-group">
                <label for="grading${gradingCount}">Component</label>
                <input type="text" id="grading${gradingCount}" name="grading${gradingCount}" placeholder="e.g., Exams">
            </div>
            <div class="form-group">
                <label for="grading${gradingCount}Percentage">Percentage</label>
                <input type="number" id="grading${gradingCount}Percentage" name="grading${gradingCount}Percentage" min="0" max="100">
            </div>
            <button type="button" class="btn-secondary remove-btn" onclick="this.parentElement.remove()">Remove</button>
        `;
        gradingContainer.appendChild(gradingItem);
    }
    
    // Function to generate the syllabus preview
    function generateSyllabus(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(syllabusForm);
        const formValues = Object.fromEntries(formData.entries());
        
        // Create syllabus HTML content
        let syllabusHTML = `
            <div class="syllabus-header">
                <h1>${formValues.courseTitle}</h1>
                ${formValues.courseCode ? `<h2>${formValues.courseCode}</h2>` : ''}
                <p>${formValues.semester} ${formValues.academicYear}</p>
            </div>
            
            <div class="syllabus-section">
                <h2>Instructor Information</h2>
                <p><strong>Instructor:</strong> ${formValues.instructorName}</p>
                ${formValues.instructorEmail ? `<p><strong>Email:</strong> ${formValues.instructorEmail}</p>` : ''}
                ${formValues.officeHours ? `<p><strong>Office Hours:</strong> ${formValues.officeHours}</p>` : ''}
                ${formValues.officeLocation ? `<p><strong>Office Location:</strong> ${formValues.officeLocation}</p>` : ''}
                ${formValues.taName ? `<p><strong>TA:</strong> ${formValues.taName}</p>` : ''}
                ${formValues.taEmail ? `<p><strong>TA Email:</strong> ${formValues.taEmail}</p>` : ''}
                ${formValues.taOfficeHours ? `<p><strong>TA Office Hours:</strong> ${formValues.taOfficeHours}</p>` : ''}
            </div>
            
            <div class="syllabus-section">
                <h2>Course Description</h2>
                <p>${formValues.courseDescription}</p>
            </div>
        `;
        
        // Add course objectives if provided
        if (formValues.courseObjectives) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Course Objectives</h2>
                    <p>${formValues.courseObjectives}</p>
                </div>
            `;
        }
        
        // Add required materials if provided
        if (formValues.requiredMaterials) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Required Materials</h2>
                    <p>${formValues.requiredMaterials}</p>
                </div>
            `;
        }

        // Add recommended materials if provided
        if (formValues.recommendedMaterials) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Recommended Materials</h2>
                    <p>${formValues.recommendedMaterials}</p>
                </div>
            `;
        }

        // Add prerequisites if provided
        if (formValues.prerequisites) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Prerequisites</h2>
                    <p>${formValues.prerequisites}</p>
                </div>
            `;
        }

        // Add course credits if provided
        if (formValues.courseCredits) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Course Credits</h2>
                    <p>${formValues.courseCredits}</p>
                </div>
            `;
        }

        // Add classroom location/mode if provided
        if (formValues.classroomLocation) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Classroom Location/Mode</h2>
                    <p>${formValues.classroomLocation}</p>
                </div>
            `;
        }
        
        // Add course schedule
        syllabusHTML += `
            <div class="syllabus-section">
                <h2>Course Schedule</h2>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Topic</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Add schedule items
        for (let i = 1; i <= weekCount; i++) {
            const weekTopic = formValues[`week${i}`] || '';
            const weekDesc = formValues[`week${i}Description`] || '';
            
            if (weekTopic || weekDesc) {
                syllabusHTML += `
                    <tr>
                        <td>Week ${i}</td>
                        <td>${weekTopic}</td>
                        <td>${weekDesc}</td>
                    </tr>
                `;
            }
        }
        
        syllabusHTML += `
                    </tbody>
                </table>
            </div>
        `;
        
        // Add grading policy
        let totalPercentage = 0;
        let hasGradingItems = false;
        
        for (let i = 1; i <= gradingCount; i++) {
            const component = formValues[`grading${i}`];
            const percentage = formValues[`grading${i}Percentage`];
            
            if (component && percentage) {
                hasGradingItems = true;
                totalPercentage += parseInt(percentage);
            }
        }
        
        if (hasGradingItems) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Grading Policy</h2>
                    <table class="grading-table">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            for (let i = 1; i <= gradingCount; i++) {
                const component = formValues[`grading${i}`];
                const percentage = formValues[`grading${i}Percentage`];
                
                if (component && percentage) {
                    syllabusHTML += `
                        <tr>
                            <td>${component}</td>
                            <td>${percentage}%</td>
                        </tr>
                    `;
                }
            }
            
            syllabusHTML += `
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong>${totalPercentage}%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            `;
        }
        
        // Add additional policies if provided
        if (formValues.attendancePolicy || formValues.academicIntegrity || formValues.accommodations) {
            syllabusHTML += `<div class="syllabus-section"><h2>Policies</h2>`;
            
            if (formValues.attendancePolicy) {
                syllabusHTML += `
                    <h3>Attendance Policy</h3>
                    <p>${formValues.attendancePolicy}</p>
                `;
            }
            
            if (formValues.academicIntegrity) {
                syllabusHTML += `
                    <h3>Academic Integrity</h3>
                    <p>${formValues.academicIntegrity}</p>
                `;
            }
            
            if (formValues.accommodations) {
                syllabusHTML += `
                    <h3>Accommodations</h3>
                    <p>${formValues.accommodations}</p>
                `;
            }
            
            syllabusHTML += `</div>`;
        }

        // Add late work policy if provided
        if (formValues.lateWorkPolicy) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Late Work Policy</h2>
                    <p>${formValues.lateWorkPolicy}</p>
                </div>
            `;
        }

        // Add other policies if provided
        if (formValues.otherPolicies) {
            syllabusHTML += `
                <div class="syllabus-section">
                    <h2>Other Policies</h2>
                    <p>${formValues.otherPolicies}</p>
                </div>
            `;
        }
        
        // Update the syllabus content and show the preview
        syllabusContent.innerHTML = syllabusHTML;
        syllabusForm.classList.add('hidden');
        syllabusPreview.classList.remove('hidden');
        downloadBtn.disabled = false;
    }
    
    // Function to go back to the edit form
    function backToEdit() {
        syllabusForm.classList.remove('hidden');
        syllabusPreview.classList.add('hidden');
    }
    
    // Function to download the syllabus as PDF
    function downloadPDF() {
        // Show loading state
        downloadBtn.textContent = 'Generating PDF...';
        downloadBtn.disabled = true;
        
        // Get course title for the filename
        const courseTitle = document.getElementById('courseTitle').value || 'Syllabus';
        const filename = courseTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '_syllabus.pdf';
        
        // Use html2canvas and jsPDF
        const { jsPDF } = window.jspdf;
        
        html2canvas(syllabusContent, {
            scale: 2,
            useCORS: true,
            logging: false
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const imgWidth = canvas.width;
            const imgHeight = canvas.height;
            const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
            const imgX = (pdfWidth - imgWidth * ratio) / 2;
            const imgY = 30;
            
            pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
            pdf.save(filename);
            
            // Reset button state
            downloadBtn.textContent = 'Download as PDF';
            downloadBtn.disabled = false;
        });
    }
});
