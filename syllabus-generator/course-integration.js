/**
 * Course Integration for Syllabus Generator
 * This script extends the syllabus generator to work with the course system
 */

// Function to initialize syllabus generator on course page
function initSyllabusGenerator(courseId, courseName) {
    // Create syllabus section on course page
    const syllabusSection = document.createElement('div');
    syllabusSection.id = 'syllabus-section';
    syllabusSection.className = 'course-section';
    syllabusSection.innerHTML = `
        <h2>Course Syllabus</h2>
        <div id="syllabus-actions">
            <button id="create-syllabus-btn" class="btn-primary">Create Syllabus</button>
            <div id="existing-syllabi" class="hidden">
                <h3>Existing Syllabi</h3>
                <div id="syllabi-list" class="syllabi-list">
                    <p>Loading syllabi...</p>
                </div>
            </div>
        </div>
        <div id="syllabus-container" class="hidden"></div>
    `;
    
    // Add syllabus section to course page
    document.querySelector('.course-content').appendChild(syllabusSection);
    
    // Add event listeners
    document.getElementById('create-syllabus-btn').addEventListener('click', () => {
        openSyllabusGenerator(courseId, courseName);
    });
    
    // Load existing syllabi for this course
    loadSyllabiForCourse(courseId);
}

// Function to load existing syllabi for a course
function loadSyllabiForCourse(courseId) {
    // Get auth token
    const token = localStorage.getItem('auth_token');
    if (!token) {
        console.error('User not authenticated');
        return;
    }
    
    // Fetch syllabi from API
    fetch(`/api/syllabus-get.php?class_id=${courseId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const syllabusListContainer = document.getElementById('syllabi-list');
        
        if (data.length === 0) {
            syllabusListContainer.innerHTML = '<p>No syllabi found for this course.</p>';
            return;
        }
        
        // Show existing syllabi section
        document.getElementById('existing-syllabi').classList.remove('hidden');
        
        // Create list of syllabi
        let syllabusListHTML = '';
        data.forEach(syllabus => {
            const createdDate = new Date(syllabus.created_on).toLocaleDateString();
            const updatedDate = new Date(syllabus.updated_on).toLocaleDateString();
            
            syllabusListHTML += `
                <div class="syllabus-item" data-id="${syllabus.id}">
                    <div class="syllabus-info">
                        <h4>${syllabus.title}</h4>
                        <p>Created: ${createdDate} | Last Updated: ${updatedDate}</p>
                    </div>
                    <div class="syllabus-actions">
                        <button class="btn-secondary view-syllabus" data-id="${syllabus.id}">View</button>
                        <button class="btn-secondary edit-syllabus" data-id="${syllabus.id}">Edit</button>
                        <button class="btn-danger delete-syllabus" data-id="${syllabus.id}">Delete</button>
                    </div>
                </div>
            `;
        });
        
        syllabusListContainer.innerHTML = syllabusListHTML;
        
        // Add event listeners to buttons
        document.querySelectorAll('.view-syllabus').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                viewSyllabus(id);
            });
        });
        
        document.querySelectorAll('.edit-syllabus').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editSyllabus(id, courseId);
            });
        });
        
        document.querySelectorAll('.delete-syllabus').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                deleteSyllabus(id, courseId);
            });
        });
    })
    .catch(error => {
        console.error('Error loading syllabi:', error);
        document.getElementById('syllabi-list').innerHTML = '<p>Error loading syllabi. Please try again later.</p>';
    });
}

// Function to open syllabus generator in a modal
function openSyllabusGenerator(courseId, courseName) {
    // Create modal container if it doesn't exist
    let modal = document.getElementById('syllabus-generator-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'syllabus-generator-modal';
        modal.className = 'modal';
        document.body.appendChild(modal);
    }
    
    // Load syllabus generator in modal
    modal.innerHTML = `
        <div class="modal-content large-modal">
            <span class="close">&times;</span>
            <h2>Create Syllabus for ${courseName}</h2>
            <iframe id="syllabus-generator-iframe" src="/new/index.html?course_id=${courseId}&course_name=${encodeURIComponent(courseName)}" frameborder="0"></iframe>
        </div>
    `;
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Add close button functionality
    modal.querySelector('.close').addEventListener('click', function() {
        modal.classList.add('hidden');
        // Reload syllabi list after closing modal
        loadSyllabiForCourse(courseId);
    });
    
    // Setup message listener for iframe communication
    window.addEventListener('message', function(event) {
        // Verify origin for security
        if (event.origin !== window.location.origin) return;
        
        if (event.data.type === 'syllabus-saved') {
            // Syllabus was saved, reload list and close modal
            loadSyllabiForCourse(courseId);
            modal.classList.add('hidden');
        }
    });
}

// Function to view a syllabus
function viewSyllabus(syllabusId) {
    // Get auth token
    const token = localStorage.getItem('auth_token');
    if (!token) {
        console.error('User not authenticated');
        return;
    }
    
    // Fetch syllabus from API
    fetch(`/api/syllabus-get.php?id=${syllabusId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(syllabus => {
        // Create modal to display syllabus
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <h2>${syllabus.title}</h2>
                <div class="syllabus-content">
                    ${syllabus.content}
                </div>
                <div class="modal-actions">
                    <button id="print-syllabus" class="btn-secondary">Print</button>
                    <button id="close-modal" class="btn-secondary">Close</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Add close button functionality
        modal.querySelector('.close').addEventListener('click', function() {
            modal.remove();
        });
        
        // Add print functionality
        document.getElementById('print-syllabus').addEventListener('click', function() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${syllabus.title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .syllabus-header { text-align: center; margin-bottom: 20px; }
                        .syllabus-section { margin-bottom: 20px; }
                        .syllabus-section h2 { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th { background-color: #f8f9fa; text-align: left; padding: 8px; }
                        td { padding: 8px; border-bottom: 1px solid #eee; }
                    </style>
                </head>
                <body>
                    ${syllabus.content}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        });
        
        document.getElementById('close-modal').addEventListener('click', function() {
            modal.remove();
        });
    })
    .catch(error => {
        console.error('Error loading syllabus:', error);
        alert('Error loading syllabus. Please try again later.');
    });
}

// Function to edit a syllabus
function editSyllabus(syllabusId, courseId) {
    // Create modal container if it doesn't exist
    let modal = document.getElementById('syllabus-generator-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'syllabus-generator-modal';
        modal.className = 'modal';
        document.body.appendChild(modal);
    }
    
    // Load syllabus generator in modal with edit mode
    modal.innerHTML = `
        <div class="modal-content large-modal">
            <span class="close">&times;</span>
            <h2>Edit Syllabus</h2>
            <iframe id="syllabus-generator-iframe" src="/new/index.html?syllabus_id=${syllabusId}&course_id=${courseId}" frameborder="0"></iframe>
        </div>
    `;
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Add close button functionality
    modal.querySelector('.close').addEventListener('click', function() {
        modal.classList.add('hidden');
        // Reload syllabi list after closing modal
        loadSyllabiForCourse(courseId);
    });
}

// Function to delete a syllabus
function deleteSyllabus(syllabusId, courseId) {
    if (!confirm('Are you sure you want to delete this syllabus? This action cannot be undone.')) {
        return;
    }
    
    // Get auth token
    const token = localStorage.getItem('auth_token');
    if (!token) {
        console.error('User not authenticated');
        return;
    }
    
    // Delete syllabus from API
    fetch('/api/syllabus-delete.php', {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: syllabusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Syllabus deleted successfully') {
            // Reload syllabi list
            loadSyllabiForCourse(courseId);
        } else {
            alert('Error deleting syllabus: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting syllabus:', error);
        alert('Error deleting syllabus. Please try again later.');
    });
}

// Modify the syllabus generator to work with course integration
function modifySyllabusGenerator() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const courseId = urlParams.get('course_id');
    const courseName = urlParams.get('course_name');
    const syllabusId = urlParams.get('syllabus_id');
    
    if (!courseId) {
        // Not in course integration mode
        return;
    }
    
    // Add course ID to form as hidden input
    const courseIdInput = document.createElement('input');
    courseIdInput.type = 'hidden';
    courseIdInput.id = 'courseId';
    courseIdInput.name = 'courseId';
    courseIdInput.value = courseId;
    document.getElementById('syllabusForm').appendChild(courseIdInput);
    
    // Pre-fill course name if available
    if (courseName) {
        document.getElementById('courseTitle').value = decodeURIComponent(courseName);
    }
    
    // Modify save function to use API instead of localStorage
    window.saveSyllabusToServer = function() {
        // Get form values
        const formData = new FormData(document.getElementById('syllabusForm'));
        const formValues = {};
        for (const [key, value] of formData.entries()) {
            // Handle array inputs (like weekNumber[], weekTopic[], etc.)
            if (key.endsWith('[]')) {
                const baseKey = key.slice(0, -2);
                if (!formValues[baseKey]) {
                    formValues[baseKey] = [];
                }
                formValues[baseKey].push(value);
            } else {
                formValues[key] = value;
            }
        }
        
        // Get auth token
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('You must be logged in to save a syllabus.');
            return;
        }
        
        // Create syllabus object
        const syllabusData = {
            title: formValues.courseTitle || 'Untitled Syllabus',
            content: document.getElementById('syllabusContent').innerHTML,
            form_data: formValues,
            class_id: courseId
        };
        
        // Add ID if editing existing syllabus
        if (syllabusId) {
            syllabusData.id = syllabusId;
        }
        
        // Save to server
        fetch('/api/syllabus-save.php', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(syllabusData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message.includes('successfully')) {
                // Notify parent window that syllabus was saved
                window.parent.postMessage({ type: 'syllabus-saved', syllabusId: data.id }, window.location.origin);
                alert('Syllabus saved successfully!');
            } else {
                alert('Error saving syllabus: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saving syllabus:', error);
            alert('Error saving syllabus. Please try again later.');
        });
    };
    
    // If editing existing syllabus, load it
    if (syllabusId) {
        loadSyllabusForEditing(syllabusId);
    }
    
    // Override the original save button to use our server save
    document.getElementById('saveBtn').addEventListener('click', function(e) {
        e.preventDefault();
        window.saveSyllabusToServer();
    });
}

// Function to load a syllabus for editing
function loadSyllabusForEditing(syllabusId) {
    // Get auth token
    const token = localStorage.getItem('auth_token');
    if (!token) {
        console.error('User not authenticated');
        return;
    }
    
    // Fetch syllabus from API
    fetch(`/api/syllabus-get.php?id=${syllabusId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(syllabus => {
        const formValues = syllabus.form_data;
        
        // Fill in basic form fields
        for (const key in formValues) {
            const input = document.getElementById(key);
            if (input && typeof formValues[key] === 'string') {
                input.value = formValues[key];
            }
        }
        
        // Clear existing schedule and grading items
        document.getElementById('scheduleContainer').innerHTML = '';
        document.getElementById('gradingContainer').innerHTML = '';
        
        // Add schedule items
        if (formValues.weekNumber && formValues.weekNumber.length > 0) {
            for (let i = 0; i < formValues.weekNumber.length; i++) {
                const weekItem = document.createElement('div');
                weekItem.className = 'week-item';
                weekItem.innerHTML = `
                    <div class="form-group">
                        <label>Week/Module Number</label>
                        <input type="text" name="weekNumber[]" value="${formValues.weekNumber[i]}">
                    </div>
                    <div class="form-group">
                        <label>Topic</label>
                        <input type="text" name="weekTopic[]" value="${formValues.weekTopic ? formValues.weekTopic[i] : ''}">
                    </div>
                    <div class="form-group">
                        <label>Description/Activities</label>
                        <textarea name="weekDescription[]">${formValues.weekDescription ? formValues.weekDescription[i] : ''}</textarea>
                    </div>
                    <button type="button" class="remove-btn">Remove</button>
                `;
                document.getElementById('scheduleContainer').appendChild(weekItem);
                
                // Add remove button functionality
                weekItem.querySelector('.remove-btn').addEventListener('click', function() {
                    weekItem.remove();
                });
            }
        }
        
        // Add grading components
        if (formValues.gradingComponent && formValues.gradingComponent.length > 0) {
            for (let i = 0; i < formValues.gradingComponent.length; i++) {
                const gradingItem = document.createElement('div');
                gradingItem.className = 'grading-item';
                gradingItem.innerHTML = `
                    <div class="form-group">
                        <label>Component</label>
                        <input type="text" name="gradingComponent[]" value="${formValues.gradingComponent[i]}">
                    </div>
                    <div class="form-group">
                        <label>Percentage</label>
                        <input type="number" name="gradingPercentage[]" min="0" max="100" value="${formValues.gradingPercentage ? formValues.gradingPercentage[i] : '0'}">
                    </div>
                    <button type="button" class="remove-btn">Remove</button>
                `;
                document.getElementById('gradingContainer').appendChild(gradingItem);
                
                // Add remove button functionality
                gradingItem.querySelector('.remove-btn').addEventListener('click', function() {
                    gradingItem.remove();
                });
            }
        }
    })
    .catch(error => {
        console.error('Error loading syllabus for editing:', error);
        alert('Error loading syllabus. Please try again later.');
    });
}

// Initialize syllabus generator modifications when the page loads
document.addEventListener('DOMContentLoaded', function() {
    modifySyllabusGenerator();
});

// Export functions for use in course pages
window.SyllabusIntegration = {
    initSyllabusGenerator,
    loadSyllabiForCourse,
    viewSyllabus,
    editSyllabus,
    deleteSyllabus
};
