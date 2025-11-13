<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern To-Do List</title>
    
    <!-- Load CSS -->
    <link rel="stylesheet" href="styles.css">
    
    <!-- Load Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body>

    <!-- Auth View (Login / Sign Up) -->
    <div id="auth-view">
        <!-- Login Form -->
        <form id="login-form">
            <h2>Welcome Back!</h2>
            <p>Log in to see your tasks.</p>
            <div class="form-group">
                <label for="login-username">Username</label>
                <input type="text" id="login-username" placeholder="Enter your username" autocomplete="off">
            </div>
            <p id="login-error" class="error-message hidden"></p>
            <button type="submit" class="btn btn-primary">
                <i class="ph-bold ph-sign-in"></i>
                Log In
            </button>
            <p class="form-link">
                Don't have an account? 
                <button type="button" id="show-signup">Sign up</button>
            </p>
        </form>

        <!-- Sign Up Form (hidden by default) -->
        <form id="signup-form" class="hidden">
            <h2>Create Account</h2>
            <p>Create a username to save your tasks.</p>
            <div class="form-group">
                <label for="signup-username">Username</label>
                <input type="text" id="signup-username" placeholder="Choose a username" autocomplete="off">
            </div>
            <p id="signup-error" class="error-message hidden"></p>
            <button type="submit" class="btn btn-primary">
                <i class="ph-bold ph-user-plus"></i>
                Sign Up & Log In
            </button>
            <p class="form-link">
                Already have an account? 
                <button type="button" id="show-login">Log in</button>
            </p>
        </form>
    </div>

    <!-- Main App View (hidden by default) -->
    <main id="app-view" class="hidden">
        <header>
            <div>
                <h1>My To-Do List</h1>
                <p id="welcome-message">Hello!</p>
            </div>
            <div class="header-actions">
                <button id="theme-toggle" class="header-btn" aria-label="Toggle light/dark mode">
                    <i class="ph-bold ph-sun theme-icon dark-icon"></i>
                    <i class="ph-bold ph-moon theme-icon light-icon"></i>
                </button>
                <button id="logout-btn" class="header-btn logout" aria-label="Log out">
                    <i class="ph-bold ph-sign-out"></i>
                </button>
            </div>
        </header>

        <!-- Task Input Form -->
        <form id="task-form">
            <div class="task-form-container">
                <label for="task-input" class="sr-only">New task</label>
                <input type="text" id="task-input" placeholder="e.g. Finish project proposal" autocomplete="off">
                <button type="submit" class="btn btn-primary">
                    <i class="ph-bold ph-plus"></i>
                    Add Task
                </button>
            </div>
        </form>

        <!-- Task List -->
        <section class="task-section">
            <div class="task-header">
                <h2>Your Tasks</h2>
                <span id="task-count">0 tasks</span>
            </div>
            <ul id="task-list">
                <!-- Task items will be dynamically inserted here -->
                
                <!-- Empty State -->
                <li id="empty-state">
                    <i class="ph-bold ph-list-checks"></i>
                    <p>All clear!</p>
                    <p>You have no pending tasks. Add one above.</p>
                </li>
            </ul>
        </section>
    </main>
    
    <footer>
        <p>Created with HTML, CSS, and PHP.</p>
    </footer>

    <!-- Edit Task Modal -->
    <div id="edit-modal" class="hidden">
        <div id="edit-modal-content">
            <h3>Edit Your Task</h3>
            <input type="text" id="edit-input" autocomplete="off">
            <div class="modal-actions">
                <button id="cancel-edit-btn" class="btn btn-secondary">Cancel</button>
                <button id="save-edit-btn" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- App Views ---
            const authView = document.getElementById('auth-view');
            const appView = document.getElementById('app-view');

            // --- Auth Elements ---
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            const loginUsernameInput = document.getElementById('login-username');
            const signupUsernameInput = document.getElementById('signup-username');
            const loginError = document.getElementById('login-error');
            const signupError = document.getElementById('signup-error');
            const showSignupBtn = document.getElementById('show-signup');
            const showLoginBtn = document.getElementById('show-login');
            const logoutBtn = document.getElementById('logout-btn');
            const welcomeMessage = document.getElementById('welcome-message');

            // --- Theme Toggle ---
            const themeToggle = document.getElementById('theme-toggle');

            // --- To-Do App Elements ---
            const taskForm = document.getElementById('task-form');
            const taskInput = document.getElementById('task-input');
            const taskList = document.getElementById('task-list');
            const taskCount = document.getElementById('task-count');
            const emptyState = document.getElementById('empty-state');
            
            // --- Edit Modal Elements ---
            const editModal = document.getElementById('edit-modal');
            const editInput = document.getElementById('edit-input');
            const saveEditBtn = document.getElementById('save-edit-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');

            let tasks = [];
            let editingTaskId = null;
            let currentUser = null;

            function initTheme() {
                const isDarkMode = localStorage.getItem('theme') === 'dark';
                if (isDarkMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            function toggleTheme() {
                if (document.documentElement.classList.toggle('dark')) {
                    localStorage.setItem('theme', 'dark');
                } else {
                    localStorage.setItem('theme', 'light');
                }
            }

            async function checkAuth() {
                try {
                    const formData = new FormData();
                    formData.append('action', 'check');
                    
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        currentUser = data.username;
                        showAppView();
                        loadTasks();
                    } else {
                        showLoginView();
                    }
                } catch (error) {
                    showLoginView();
                }
            }

            async function handleLogin(e) {
                e.preventDefault();
                const username = loginUsernameInput.value.trim().toLowerCase();
                
                if (!username) {
                    loginError.textContent = 'Username cannot be empty.';
                    loginError.classList.remove('hidden');
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'login');
                    formData.append('username', username);
                    
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        loginError.classList.add('hidden');
                        currentUser = data.username;
                        showAppView();
                        loadTasks();
                    } else {
                        loginError.textContent = data.message || 'Login failed.';
                        loginError.classList.remove('hidden');
                        loginForm.classList.add('animate-shake');
                        setTimeout(() => loginForm.classList.remove('animate-shake'), 820);
                    }
                } catch (error) {
                    loginError.textContent = 'An error occurred. Please try again.';
                    loginError.classList.remove('hidden');
                }
            }

            async function handleSignUp(e) {
                e.preventDefault();
                const username = signupUsernameInput.value.trim().toLowerCase();
                
                if (!username) {
                    signupError.textContent = 'Username cannot be empty.';
                    signupError.classList.remove('hidden');
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('action', 'signup');
                    formData.append('username', username);
                    
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        signupError.classList.add('hidden');
                        currentUser = data.username;
                        showAppView();
                        loadTasks();
                    } else {
                        signupError.textContent = data.message || 'Signup failed.';
                        signupError.classList.remove('hidden');
                        signupForm.classList.add('animate-shake');
                        setTimeout(() => signupForm.classList.remove('animate-shake'), 820);
                    }
                } catch (error) {
                    signupError.textContent = 'An error occurred. Please try again.';
                    signupError.classList.remove('hidden');
                }
            }

            async function handleLogout() {
                try {
                    const formData = new FormData();
                    formData.append('action', 'logout');
                    
                    await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });
                } catch (error) {
                    // Silent fail
                } finally {
                    currentUser = null;
                    tasks = [];
                    showLoginView();
                }
            }

            function showLoginView() {
                authView.classList.remove('hidden');
                appView.classList.add('hidden');
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                loginUsernameInput.value = '';
                signupUsernameInput.value = '';
            }

            function showSignUpView() {
                authView.classList.remove('hidden');
                appView.classList.add('hidden');
                loginForm.classList.add('hidden');
                signupForm.classList.remove('hidden');
                loginUsernameInput.value = '';
                signupUsernameInput.value = '';
            }

            function showAppView() {
                authView.classList.add('hidden');
                appView.classList.remove('hidden');
                welcomeMessage.textContent = `Hello, ${currentUser}!`;
            }

            function renderTasks() {
                taskList.innerHTML = '';

                if (tasks.length === 0) {
                    taskList.appendChild(emptyState);
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    tasks.forEach(task => {
                        const li = createTaskElement(task);
                        li.classList.add('task-item-enter');
                        taskList.appendChild(li);
                    });
                }
                updateTaskCount();
            }

            function createTaskElement(task) {
                const li = document.createElement('li');
                li.className = `task-item ${task.completed ? 'completed' : ''}`;
                li.setAttribute('data-id', task.id);

                li.innerHTML = `
                    <div class="task-item-content">
                        <input type="checkbox" class="task-checkbox" ${task.completed ? 'checked' : ''}>
                        <span class="task-text">${escapeHTML(task.text)}</span>
                    </div>
                    <div class="task-actions">
                        <button class="task-btn edit" aria-label="Edit task">
                            <i class="ph-bold ph-pencil-simple"></i>
                        </button>
                        <button class="task-btn delete" aria-label="Delete task">
                            <i class="ph-bold ph-trash-simple"></i>
                        </button>
                    </div>
                `;
                return li;
            }

            function updateTaskCount() {
                const pendingTasks = tasks.filter(task => !task.completed).length;
                if (pendingTasks === 1) {
                    taskCount.textContent = '1 task pending';
                } else {
                    taskCount.textContent = `${pendingTasks} tasks pending`;
                }
            }

            async function loadTasks() {
                if (!currentUser) return;
                
                try {
                    const response = await fetch('tasks.php?action=get');
                    const data = await response.json();
                    
                    if (data.success) {
                        tasks = data.tasks || [];
                        renderTasks();
                    } else {
                        tasks = [];
                        renderTasks();
                    }
                } catch (error) {
                    tasks = [];
                    renderTasks();
                }
            }

            function escapeHTML(str) {
                return str.replace(/[&<>"']/g, (match) => {
                    const escape = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;',
                    };
                    return escape[match];
                });
            }

            async function handleAddTask(e) {
                e.preventDefault();
                const text = taskInput.value.trim();
                
                if (text === '') {
                    taskInput.classList.add('border-red-500', 'animate-shake');
                    setTimeout(() => {
                        taskInput.classList.remove('border-red-500', 'animate-shake');
                    }, 500);
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('action', 'add');
                    formData.append('text', text);
                    
                    const response = await fetch('tasks.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const newTask = data.task;
                        tasks.unshift(newTask); // Add to the beginning
                        
                        if (tasks.length === 1) {
                            renderTasks(); // Re-render to remove empty state
                        } else {
                            const li = createTaskElement(newTask);
                            li.classList.add('task-item-enter');
                            taskList.prepend(li);
                            emptyState.classList.add('hidden');
                        }
                        
                        updateTaskCount();
                        taskInput.value = '';
                    } else {
                        alert('Error adding task: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    alert('An error occurred while adding the task.');
                }
            }

            function handleTaskListClick(e) {
                const target = e.target;
                const li = target.closest('.task-item');
                if (!li) return;

                const id = li.getAttribute('data-id');

                if (target.classList.contains('task-checkbox')) {
                    handleToggleComplete(id, target.checked, li);
                }
                if (target.closest('.task-btn.delete')) {
                    handleDeleteTask(id, li);
                }
                if (target.closest('.task-btn.edit')) {
                    handleOpenEditModal(id);
                }
            }

            async function handleToggleComplete(id, isCompleted, li) {
                const task = tasks.find(t => t.id === id);
                if (!task) return;
                
                // Optimistically update UI
                task.completed = isCompleted;
                li.classList.toggle('completed', isCompleted);
                updateTaskCount();
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'toggle');
                    formData.append('id', id);
                    formData.append('completed', isCompleted ? 1 : 0);
                    
                    const response = await fetch('tasks.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        task.completed = !isCompleted;
                        li.classList.toggle('completed', !isCompleted);
                        updateTaskCount();
                    }
                } catch (error) {
                    task.completed = !isCompleted;
                    li.classList.toggle('completed', !isCompleted);
                    updateTaskCount();
                }
            }

            async function handleDeleteTask(id, li) {
                li.classList.add('task-item-exit');
                
                setTimeout(async () => {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', id);
                        
                        const response = await fetch('tasks.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            tasks = tasks.filter(task => task.id !== id);
                            li.remove();
                            updateTaskCount();
                            
                            if (tasks.length === 0) {
                                emptyState.classList.remove('hidden');
                            }
                        } else {
                            li.classList.remove('task-item-exit');
                        }
                    } catch (error) {
                        li.classList.remove('task-item-exit');
                    }
                }, 300);
            }

            function handleOpenEditModal(id) {
                const task = tasks.find(t => t.id === id);
                if (!task) return;
                
                editingTaskId = id;
                editInput.value = task.text;
                editModal.classList.remove('hidden');
                editInput.focus();
            }

            function handleCloseEditModal() {
                editModal.classList.add('hidden');
                editingTaskId = null;
                editInput.value = '';
            }

            async function handleSaveEdit() {
                const newText = editInput.value.trim();
                
                if (newText === '') {
                    editInput.classList.add('border-red-500');
                    setTimeout(() => editInput.classList.remove('border-red-500'), 500);
                    return;
                }

                const task = tasks.find(t => t.id === editingTaskId);
                if (!task) {
                    handleCloseEditModal();
                    return;
                }
                
                const oldText = task.text;
                
                // Optimistically update UI
                task.text = newText;
                const li = taskList.querySelector(`[data-id="${editingTaskId}"]`);
                if (li) {
                    li.querySelector('.task-text').textContent = escapeHTML(newText);
                }
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('id', editingTaskId);
                    formData.append('text', newText);
                    
                    const response = await fetch('tasks.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        // Revert on error
                        task.text = oldText;
                        if (li) {
                            li.querySelector('.task-text').textContent = escapeHTML(oldText);
                        }
                        alert('Error updating task: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    task.text = oldText;
                    if (li) {
                        li.querySelector('.task-text').textContent = escapeHTML(oldText);
                    }
                    alert('An error occurred while updating the task.');
                }
                
                handleCloseEditModal();
            }
            
            themeToggle.addEventListener('click', toggleTheme);
            loginForm.addEventListener('submit', handleLogin);
            signupForm.addEventListener('submit', handleSignUp);
            logoutBtn.addEventListener('click', handleLogout);
            showSignupBtn.addEventListener('click', showSignUpView);
            showLoginBtn.addEventListener('click', showLoginView);
            taskForm.addEventListener('submit', handleAddTask);
            taskList.addEventListener('click', handleTaskListClick);
            saveEditBtn.addEventListener('click', handleSaveEdit);
            cancelEditBtn.addEventListener('click', handleCloseEditModal);
            editModal.addEventListener('click', (e) => {
                if (e.target === editModal) handleCloseEditModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !editModal.classList.contains('hidden')) {
                    handleCloseEditModal();
                }
            });

            initTheme();
            authView.classList.remove('hidden');
            appView.classList.add('hidden');
            editModal.classList.add('hidden');
            checkAuth();
        });
    </script>
</body>
</html>

