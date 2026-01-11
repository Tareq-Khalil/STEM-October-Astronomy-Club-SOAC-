// Global state
let currentTeam = null;
let currentQuestionStatuses = {};

// Page navigation
function showPage(pageId) {
    document.querySelectorAll('.card').forEach(card => card.classList.add('hidden'));
    document.getElementById(pageId).classList.remove('hidden');
    
    if (pageId === 'leaderboard') {
        refreshLeaderboard();
    } else if (pageId === 'teamDashboard' && currentTeam) {
        refreshTeamStatus();
    }
}

function showAdminSection(section) {
    document.querySelectorAll('.admin-section').forEach(panel => panel.classList.add('hidden'));
    
    if (section === 'teams') {
        document.getElementById('adminTeams').classList.remove('hidden');
        loadTeamsList();
    } else if (section === 'questions') {
        document.getElementById('adminQuestions').classList.remove('hidden');
        loadQuestionAnswers();
    } else if (section === 'submissions') {
        document.getElementById('adminSubmissions').classList.remove('hidden');
        loadPendingSubmissions();
    }
}

// Helper functions
function showMessage(elementId, message, type = 'success') {
    const element = document.getElementById(elementId);
    const className = type === 'success' ? 'success-message' : type === 'error' ? 'error-message' : 'info-message';
    element.innerHTML = `<div class="${className}">${message}</div>`;
    setTimeout(() => element.innerHTML = '', 5000);
}

async function apiCall(action, data = {}, isFormData = false) {
    try {
        let body;
        if (isFormData) {
            body = data;
        } else {
            body = new FormData();
            body.append('action', action);
            for (let key in data) {
                body.append(key, data[key]);
            }
        }
        
        const response = await fetch('api.php', {
            method: 'POST',
            body: body
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

// Admin functions
async function loginAdmin() {
    const password = document.getElementById('adminPassword').value;
    const result = await apiCall('admin_login', { password });
    
    if (result.success) {
        showPage('adminPanel');
        showAdminSection('teams');
        document.getElementById('adminPassword').value = '';
    } else {
        showMessage('adminLoginMessage', result.message, 'error');
    }
}

async function adminLogout() {
    await apiCall('admin_logout');
    document.getElementById('adminPassword').value = '';
    showPage('landingPage');
}

async function addTeam() {
    const teamName = document.getElementById('newTeamName').value.trim();
    const passcode = document.getElementById('newTeamPasscode').value.trim();
    
    if (!teamName || !passcode) {
        showMessage('addTeamMessage', 'Please enter both team name and passcode', 'error');
        return;
    }
    
    const result = await apiCall('add_team', { team_name: teamName, passcode: passcode });
    
    if (result.success) {
        document.getElementById('newTeamName').value = '';
        document.getElementById('newTeamPasscode').value = '';
        showMessage('addTeamMessage', result.message, 'success');
        loadTeamsList();
    } else {
        showMessage('addTeamMessage', result.message, 'error');
    }
}

async function loadTeamsList() {
    const result = await apiCall('get_teams');
    const container = document.getElementById('teamsList');
    
    if (result.success && result.teams.length > 0) {
        container.innerHTML = result.teams.map(team => {
            const score = parseFloat(team.score);
            const scoreDisplay = (isNaN(score) || score < 0) ? 'DQ' : `${score} points`;
            return `
            <div class="team-card">
                <strong>${team.team_name}</strong><br>
                <span style="color: #666;">Score: ${scoreDisplay}</span><br>
                <span style="color: #999; font-size: 12px;">Added: ${new Date(team.created_at).toLocaleDateString()}</span><br>
                <button onclick="deleteTeam(${team.id})" class="btn-danger" style="margin-top: 10px;">Delete Team</button>
            </div>
            `;
        }).join('');
    } else {
        container.innerHTML = '<p class="info-text">No teams added yet.</p>';
    }
}

async function deleteTeam(teamId) {
    if (!confirm('Are you sure you want to delete this team? This will remove all their submissions.')) {
        return;
    }
    
    const result = await apiCall('delete_team', { team_id: teamId });
    
    if (result.success) {
        loadTeamsList();
    } else {
        alert(result.message);
    }
}

async function loadQuestionAnswers() {
    const result = await apiCall('get_questions');
    const container = document.getElementById('questionAnswers');
    
    if (result.success) {
        container.innerHTML = result.questions
            .filter(q => q.question_number <= 25)
            .map(q => `
                <div class="question-item">
                    <label><strong>Question ${q.question_number}:</strong></label>
                    <input type="number" step="any" id="answer_${q.question_number}" 
                           value="${q.correct_answer || ''}" 
                           placeholder="Correct answer" 
                           style="margin-bottom: 5px;">
                    <input type="number" step="any" id="range_${q.question_number}" 
                           value="${q.acceptable_range || 0}" 
                           placeholder="Acceptable range (±)" 
                           min="0">
                    <small style="display: block; color: #666; margin-top: 5px;">
                        Range: If answer is 100 and range is 5, accepts 95-105
                    </small>
                </div>
            `).join('');
    }
}

async function saveQuestionAnswers() {
    const questions = {};
    
    for (let i = 1; i <= 25; i++) {
        const answer = document.getElementById(`answer_${i}`).value;
        const range = document.getElementById(`range_${i}`).value;
        questions[i] = { answer, range };
    }
    
    const formData = new FormData();
    formData.append('action', 'save_question_answers');
    formData.append('questions', JSON.stringify(questions));
    
    const result = await apiCall('save_question_answers', { questions: JSON.stringify(questions) });
    
    if (result.success) {
        showMessage('saveAnswersMessage', result.message, 'success');
    } else {
        showMessage('saveAnswersMessage', result.message, 'error');
    }
}

async function loadPendingSubmissions() {
    const result = await apiCall('get_pending_submissions');
    const container = document.getElementById('submissionsList');
    
    if (result.success && result.submissions.length > 0) {
        container.innerHTML = result.submissions.map(sub => `
            <div class="question-item">
                <strong>${sub.team_name} - Question ${sub.question_number}</strong><br>
                <span style="color: #666;">File: ${sub.file_name}</span><br>
                <span style="color: #999; font-size: 12px;">Submitted: ${new Date(sub.submitted_at).toLocaleString()}</span><br>
                <div style="margin-top: 10px;">
                    <button onclick="reviewSubmission(${sub.id}, true)" class="btn-success">
                        ✓ Approve (Award 10 Points)
                    </button>
                    <button onclick="reviewSubmission(${sub.id}, false)" class="btn-danger">
                        ✗ Reject
                    </button>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = '<p class="info-text">No pending file submissions.</p>';
    }
}

async function reviewSubmission(submissionId, approved) {
    const result = await apiCall('review_submission', { 
        submission_id: submissionId, 
        approved: approved 
    });
    
    if (result.success) {
        alert(result.message);
        loadPendingSubmissions();
    } else {
        alert(result.message);
    }
}

// Team functions
async function loginTeam() {
    const teamName = document.getElementById('teamNameInput').value.trim();
    const passcode = document.getElementById('teamPasscodeInput').value;
    
    if (!teamName || !passcode) {
        showMessage('teamLoginMessage', 'Please enter team name and passcode', 'error');
        return;
    }
    
    const result = await apiCall('team_login', { team_name: teamName, passcode: passcode });
    
    if (result.success) {
        currentTeam = result.team;
        document.getElementById('teamNameInput').value = '';
        document.getElementById('teamPasscodeInput').value = '';
        loadTeamDashboard();
        showPage('teamDashboard');
    } else {
        showMessage('teamLoginMessage', result.message, 'error');
    }
}

async function teamLogout() {
    await apiCall('team_logout');
    currentTeam = null;
    currentQuestionStatuses = {};
    showPage('landingPage');
}

async function loadTeamDashboard() {
    const score = parseFloat(currentTeam.score);
    const scoreDisplay = (isNaN(score) || score < 0) ? 'DQ' : `${score} points`;
    document.getElementById('teamNameDisplay').textContent = currentTeam.name;
    document.getElementById('teamScore').textContent = scoreDisplay;
    
    // Populate question select
    const select = document.getElementById('questionSelect');
    select.innerHTML = '<option value="">-- Select Question --</option>';
    
    for (let i = 1; i <= 30; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Question ${i} ${i <= 25 ? '(Numerical)' : '(File Upload)'}`;
        select.appendChild(option);
    }
    
    await refreshTeamStatus();
}

async function refreshTeamStatus() {
    const result = await apiCall('get_team_status');
    
    if (result.success) {
        currentTeam.score = result.team.score;
        const score = parseFloat(result.team.score);
        const scoreDisplay = (isNaN(score) || score < 0) ? 'DQ' : `${score} points`;
        document.getElementById('teamScore').textContent = scoreDisplay;
        
        // Store statuses
        currentQuestionStatuses = {};
        for (let qNum in result.statuses) {
            const statusArray = result.statuses[qNum];
            if (statusArray && statusArray.length > 0) {
                currentQuestionStatuses[qNum] = statusArray[0];
            }
        }
        
        loadTeamProgress(result.statuses, result.pending);
    }
}

function updateQuestionInfo() {
    const questionNum = parseInt(document.getElementById('questionSelect').value);
    const infoDiv = document.getElementById('questionInfo');
    
    if (!questionNum) {
        infoDiv.classList.add('hidden');
        return;
    }
    
    infoDiv.classList.remove('hidden');
    
    const status = currentQuestionStatuses[questionNum] || { attempts_used: 0, is_solved: 0 };
    const attemptsLeft = 3 - parseInt(status.attempts_used);
    
    let attemptsClass = 'attempts';
    if (attemptsLeft === 0) attemptsClass += ' no-attempts';
    else if (attemptsLeft === 3) attemptsClass += ' full-attempts';
    
    let statusText = '';
    if (status.is_solved == 1) {
        statusText = '<span class="status-correct">✓ Correct!</span>';
    } else if (attemptsLeft === 0) {
        statusText = '<span class="status-incorrect">✗ No attempts left</span>';
    }
    
    document.getElementById('questionDetails').innerHTML = `
        <strong>Question ${questionNum}</strong>
        <span class="${attemptsClass}">${attemptsLeft} attempts left</span><br>
        ${statusText}
    `;
    
    // Show appropriate input
    document.getElementById('numericalAnswer').classList.add('hidden');
    document.getElementById('fileAnswer').classList.add('hidden');
    document.getElementById('answerInput').value = '';
    document.getElementById('fileName').textContent = '';
    document.getElementById('submissionMessage').innerHTML = '';
    
    if (attemptsLeft > 0 && status.is_solved != 1) {
        if (questionNum <= 25) {
            document.getElementById('numericalAnswer').classList.remove('hidden');
        } else {
            document.getElementById('fileAnswer').classList.remove('hidden');
        }
    }
}

async function submitAnswer() {
    const questionNum = parseInt(document.getElementById('questionSelect').value);
    const answer = document.getElementById('answerInput').value;
    
    if (!answer) {
        showMessage('submissionMessage', 'Please enter an answer', 'error');
        return;
    }
    
    const result = await apiCall('submit_answer', { 
        question_number: questionNum, 
        answer: answer 
    });
    
    if (result.success) {
        showMessage('submissionMessage', result.message, result.correct ? 'success' : 'error');
        await refreshTeamStatus();
        updateQuestionInfo();
    } else {
        showMessage('submissionMessage', result.message, 'error');
    }
}

function handleFileSelect() {
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    
    if (fileInput.files.length > 0) {
        fileName.textContent = `Selected: ${fileInput.files[0].name}`;
    }
}

async function submitFileAnswer() {
    const questionNum = parseInt(document.getElementById('questionSelect').value);
    const fileInput = document.getElementById('fileInput');
    
    if (fileInput.files.length === 0) {
        showMessage('submissionMessage', 'Please select a file', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'submit_file');
    formData.append('question_number', questionNum);
    formData.append('file', fileInput.files[0]);
    
    const result = await apiCall('submit_file', formData, true);
    
    if (result.success) {
        showMessage('submissionMessage', result.message, 'success');
        fileInput.value = '';
        document.getElementById('fileName').textContent = '';
        await refreshTeamStatus();
        updateQuestionInfo();
    } else {
        showMessage('submissionMessage', result.message, 'error');
    }
}

function loadTeamProgress(statuses, pending) {
    const container = document.getElementById('teamProgress');
    container.innerHTML = '';
    
    for (let i = 1; i <= 30; i++) {
        const status = statuses[i] ? statuses[i][0] : { attempts_used: 0, is_solved: 0 };
        const attemptsUsed = parseInt(status.attempts_used);
        const isSolved = status.is_solved == 1;
        const isPending = pending.includes(i);
        
        let statusText = '⭕ Not attempted';
        let color = '#666';
        
        if (isSolved) {
            statusText = '✓ Correct';
            color = '#28a745';
        } else if (isPending) {
            statusText = '⏳ Pending review';
            color = '#17a2b8';
        } else if (attemptsUsed > 0 && attemptsUsed < 3) {
            statusText = `⚠ ${attemptsUsed} attempt(s) used`;
            color = '#ffc107';
        } else if (attemptsUsed >= 3) {
            statusText = '✗ Failed';
            color = '#dc3545';
        }
        
        const div = document.createElement('div');
        div.className = 'question-item';
        div.innerHTML = `
            <strong>Question ${i}</strong> 
            <span style="color: ${color}; float: right;">${statusText}</span>
        `;
        container.appendChild(div);
    }
}

// Leaderboard
async function refreshLeaderboard() {
    const result = await apiCall('get_leaderboard');
    const tbody = document.getElementById('leaderboardBody');
    
    if (result.success && result.leaderboard.length > 0) {
        tbody.innerHTML = result.leaderboard.map((team, index) => {
            const rank = index + 1;
            let rankClass = 'rank';
            if (rank === 1) rankClass += ' rank-1';
            else if (rank === 2) rankClass += ' rank-2';
            else if (rank === 3) rankClass += ' rank-3';
            
            const score = parseFloat(team.score);
            const scoreDisplay = (isNaN(score) || score < 0) ? 'DQ' : `${score} points`;
            
            return `
                <tr>
                    <td><span class="${rankClass}">${rank}</span></td>
                    <td><strong>${team.team_name}</strong></td>
                    <td>${scoreDisplay}</td>
                </tr>
            `;
        }).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #666;">No teams yet</td></tr>';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Quiz Competition System Loaded');
});