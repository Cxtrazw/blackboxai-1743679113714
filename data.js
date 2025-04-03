// Complaint management functions
const API_BASE = 'api/';

async function handleRequest(endpoint, method = 'GET', body = null) {
    try {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        if (body) options.body = JSON.stringify(body);
        
        const response = await fetch(API_BASE + endpoint, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API request failed:', error);
        return { success: false, message: error.message };
    }
}

// Save new complaint
async function saveComplaint(complaint) {
    return handleRequest('save_complaint.php', 'POST', complaint);
}

// Get complaints by department
async function getComplaintsByDepartment(department) {
    const result = await handleRequest(`get_complaints.php?department=${department}`);
    return result.success ? result.complaints : [];
}

// Update complaint status
async function updateComplaintStatus(id, status) {
    return handleRequest('update_complaint.php', 'POST', { id, status });
}

// Delete complaint (admin only)
async function deleteComplaint(id) {
    return handleRequest('delete_complaint.php', 'POST', { id });
}

// Department and priority options
const departments = [
    { value: 'facilities', label: 'Facilities Management' },
    { value: 'security', label: 'Security' },
    { value: 'operations', label: 'Operations' }
];

const priorities = [
    { value: 'Low', label: 'Low' },
    { value: 'Medium', label: 'Medium' },
    { value: 'High', label: 'High' }
];

// Utility function to format date
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Export functions if using modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        saveComplaint,
        getComplaintsByDepartment,
        updateComplaintStatus,
        deleteComplaint,
        departments,
        priorities,
        formatDate
    };
}