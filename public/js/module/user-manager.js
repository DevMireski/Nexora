(function(window, $){
    const UserManager = {
        apiBase: '/api/v1/users',
        token() { return localStorage.getItem('nexora_token'); },
        headers() { return { Authorization: 'Bearer ' + this.token() }; },
        list(page = 1, q = ''){
            return $.ajax({ url: this.apiBase, method: 'GET', data: { page, q }, headers: this.headers() });
        }
    };
    window.UserManager = UserManager;
})(window, jQuery);
const UserManager = (() => {
    const selectors = {
        table: '#users-table',
        btnSave: '#btn-save-user',
        form: '#user-form'
    };

    const init = () => {
        loadUsers();
        bindEvents();
    };

    const bindEvents = () => {
        $(selectors.btnSave).on('click', async function() {
            const data = $(selectors.form).serialize();
            await saveUser(data);
        });
    };

    const loadUsers = async () => {
        try {
            const response = await $.ajax({
                url: '/api/v1/users',
                method: 'GET',
                headers: { 'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` }
            });
            renderTable(response.data);
        } catch (error) {
            Toast.error('Erro ao carregar usuários');
        }
    };

    const renderTable = (users) => {
        // Manipulação de DOM eficiente com arrays e join
        const rows = users.map(user => `
            <tr>
                <td>${user.name}</td>
                <td><span class="badge bg-info">${user.role}</span></td>
                <td>${user.status_label}</td>
            </tr>
        `).join('');
        $(selectors.table).find('tbody').html(rows);
    };

    return { init };
})();

$(document).ready(() => UserManager.init());