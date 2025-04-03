// Sample users data (in production, this would be from a database)
const users = [
    { username: 'admin', password: 'admin123', department: 'admin', name: 'System Admin' },
    { username: 'facilities1', password: 'fac123', department: 'facilities', name: 'Facilities Manager' },
    { username: 'security1', password: 'sec123', department: 'security', name: 'Security Officer' },
    { username: 'operations1', password: 'ops123', department: 'operations', name: 'Operations Staff' }
];

// Initialize localStorage for complaints if not exists
if (!localStorage.getItem('complaints')) {
    localStorage.setItem('complaints', JSON.stringify([]));
}

// Handle login form submission
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const department = document.getElementById('department').value;
    
    // Validate credentials
    const user = users.find(u => 
        u.username === username && 
        u.password === password && 
        u.department === department
    );
    
    if (user) {
        // Store user session
        sessionStorage.setItem('currentUser', JSON.stringify(user));
        
        // Redirect based on role
        if (user.department === 'admin') {
            window.location.href = 'admin.html';
        } else {
            window.location.href = 'dashboard.html';
        }
    } else {
        alert('Invalid credentials. Please try again.');
    }
});

// Utility function to check if user is logged in
function checkAuth() {
    const user = JSON.parse(sessionStorage.getItem('currentUser'));
    if (!user) {
        window.location.href = 'index.html';
    }
    return user;
}

// Utility function to get current user
function getCurrentUser() {
    return JSON.parse(sessionStorage.getItem('currentUser'));
}

// Logout function
function logout() {
    sessionStorage.removeItem('currentUser');
    window.location.href = 'index.html';
}