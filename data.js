// Complaint management functions
const complaints = JSON.parse(localStorage.getItem('complaints')) || [];

function saveComplaint(complaint) {
    complaint.id = Date.now().toString();
    complaint.status = 'Pending';
    complaint.createdAt = new Date().toISOString();
    complaints.push(complaint);
    localStorage.setItem('complaints', JSON.stringify(complaints));
    return complaint;
}

function getComplaintsByDepartment(department) {
    if (department === 'admin') {
        return [...complaints].reverse(); // Show newest first for admin
    }
    return complaints.filter(c => c.department === department).reverse();
}

function updateComplaintStatus(id, status) {
    const complaint = complaints.find(c => c.id === id);
    if (complaint) {
        complaint.status = status;
        complaint.resolvedAt = status === 'Resolved' ? new Date().toISOString() : null;
        localStorage.setItem('complaints', JSON.stringify(complaints));
        return true;
    }
    return false;
}

function deleteComplaint(id) {
    const index = complaints.findIndex(c => c.id === id);
    if (index !== -1) {
        complaints.splice(index, 1);
        localStorage.setItem('complaints', JSON.stringify(complaints));
        return true;
    }
    return false;
}

// Department options for forms
const departments = [
    { value: 'facilities', label: 'Facilities Management' },
    { value: 'security', label: 'Security' },
    { value: 'operations', label: 'Operations' }
];

// Priority options for forms
const priorities = [
    { value: 'Low', label: 'Low' },
    { value: 'Medium', label: 'Medium' },
    { value: 'High', label: 'High' }
];

// Utility function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}