@extends('layouts.nav')

@section('title', 'Painel de administração')

@section('content')
    @php
        $roleLabels = [
            'student' => 'Aluno',
            'teacher' => 'Professor',
            'admin' => 'Administrador',
        ];
    @endphp

    <div class="page-heading">
        <p>Painel de administração</p>
        <h1>Controlos do Kuri.</h1>
        <span>Gere utilizadores e vê os grupos de curso existentes no Kuri.</span>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="admin-stats-grid">
        <div>
            <small>Total de utilizadores</small>
            <strong>{{ $stats['users'] }}</strong>
        </div>
        <div>
            <small>Alunos</small>
            <strong>{{ $stats['students'] }}</strong>
        </div>
        <div>
            <small>Professores</small>
            <strong>{{ $stats['teachers'] }}</strong>
        </div>
        <div>
            <small>Administradores</small>
            <strong>{{ $stats['admins'] }}</strong>
        </div>
        <div>
            <small>Grupos</small>
            <strong>{{ $stats['groups'] }}</strong>
        </div>
    </section>

    <section class="admin-layout">
        <div class="admin-panel-card">
            <div class="admin-section-heading">
                <div>
                    <p>Utilizadores</p>
                    <h2>Todas as contas</h2>
                </div>
                <span>{{ $users->count() }} utilizadores</span>
            </div>

            <div class="admin-user-tools">
                <input class="admin-search" id="admin-user-search" type="text" placeholder="Pesquisar utilizadores por nome, email, função, curso..." autocomplete="off">

                <select class="admin-filter-select" id="admin-role-filter">
                    <option value="">Todas as funções</option>
                    <option value="student">Alunos</option>
                    <option value="teacher">Professores</option>
                    <option value="admin">Administradores</option>
                </select>
            </div>

            <div class="admin-user-list">
                @foreach ($users as $user)
                    <div
                        class="admin-user-row"
                        data-admin-user-row
                        data-admin-role="{{ $user->role }}"
                        data-admin-search="{{ strtolower($user->name.' '.$user->email.' '.$user->role.' '.($user->course?->name ?? '').' '.($user->schoolClass?->name ?? '')) }}"
                    >
                        <a href="#user-info-{{ $user->id }}" class="admin-user-main">
                            <div class="admin-user-name-line">
                                <strong>{{ $user->name }}</strong>
                                <span class="admin-role-pill role-{{ $user->role }}">{{ $roleLabels[$user->role] ?? $user->role }}</span>
                            </div>
                            <small>{{ $user->email }}</small>
                            <span>
                                @if ($user->isAdmin())
                                    Conta de administrador
                                @else
                                    {{ $user->course?->name ?? 'Sem curso' }}
                                    ·
                                    {{ $user->schoolClass?->name ?? 'Sem turma' }}
                                @endif
                            </span>
                        </a>

                        <form
                            class="admin-role-form"
                            method="POST"
                            action="{{ route('admin.users.role', $user) }}"
                            data-role-form
                            data-user-name="{{ $user->name }}"
                            data-current-role="{{ $user->role }}"
                        >
                            @csrf
                            @method('PATCH')

                            <label>
                                <span>Função</span>
                                <select class="admin-role-select role-{{ $user->role }}" name="role">
                                    <option value="student" @selected($user->role === 'student')>Aluno</option>
                                    <option value="teacher" @selected($user->role === 'teacher')>Professor</option>
                                    <option value="admin" @selected($user->role === 'admin')>Administrador</option>
                                </select>
                            </label>

                            <button type="submit">Guardar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="admin-panel-card">
            <div class="admin-section-heading">
                <div>
                    <p>Grupos</p>
                    <h2>Todos os grupos</h2>
                </div>
                <span>{{ $groups->count() + $teacherGroups->count() }} grupos</span>
            </div>

            <input class="admin-search" id="admin-group-search" type="text" placeholder="Pesquisar grupos..." autocomplete="off">

            <div class="admin-group-list">
                <p>Grupos de curso</p>
                @foreach ($groups as $group)
                    <div data-admin-group-row data-admin-search="{{ strtolower($group->name.' course group') }}">
                        <strong>{{ $group->name }}</strong>
                        <small>{{ $group->users_count + $adminCount }} membros incluindo administradores</small>
                    </div>
                @endforeach

                <p>Grupos de professores</p>
                @forelse ($teacherGroups as $group)
                    <div data-admin-group-row data-admin-search="{{ strtolower($group->name.' '.$group->teacher->name.' teacher group') }}">
                        <strong>{{ $group->name }}</strong>
                        <small>{{ $group->members_count + $adminCount + 1 }} membros incluindo administradores · {{ $group->teacher->name }}</small>
                    </div>
                @empty
                    <small>Ainda não há grupos de professores.</small>
                @endforelse
            </div>
        </aside>
    </section>

    @foreach ($users as $user)
        <div id="user-info-{{ $user->id }}" class="admin-user-overlay">
            <div class="admin-user-box">
                <div class="admin-user-box-top">
                    <div>
                        <p>Informação do utilizador</p>
                        <h2>{{ $user->name }}</h2>
                    </div>
                    <a href="#">Fechar</a>
                </div>

                <div class="admin-info-grid">
                    <div>
                        <small>Email</small>
                        <strong>{{ $user->email }}</strong>
                    </div>

                    <div>
                        <small>Função</small>
                        <strong>{{ $roleLabels[$user->role] ?? $user->role }}</strong>
                    </div>

                    <div>
                        <small>Grupo de curso</small>
                        <strong>{{ $user->isAdmin() ? 'Conta de administrador' : ($user->course?->name ?? 'Sem curso') }}</strong>
                    </div>

                    <div>
                        <small>Turma</small>
                        <strong>{{ $user->isAdmin() ? 'Conta de administrador' : ($user->schoolClass?->name ?? 'Sem turma') }}</strong>
                    </div>

                    <div>
                        <small>Conta criada</small>
                        <strong>{{ $user->created_at->format('d M Y H:i') }}</strong>
                    </div>

                    <div>
                        <small>Última atualização</small>
                        <strong>{{ $user->updated_at->format('d M Y H:i') }}</strong>
                    </div>
                </div>

                @if ($user->id !== auth()->id())
                    <form class="admin-delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm(@json('Eliminar esta conta de utilizador? Isto não pode ser desfeito.'));">
                        @csrf
                        @method('DELETE')

                        <button type="submit">Eliminar utilizador</button>
                    </form>
                @else
                    <p class="admin-self-note">Esta é a tua conta, por isso não pode ser eliminada aqui.</p>
                @endif
            </div>
        </div>
    @endforeach

    <div class="admin-confirm-overlay" id="admin-role-confirm">
        <div class="admin-confirm-box">
            <p>Confirmar alteração de função</p>
            <h2>Tens a certeza?</h2>
            <span id="admin-role-confirm-text">Isto vai alterar a função do utilizador.</span>

            <div>
                <button type="button" class="admin-confirm-cancel" id="admin-role-cancel">Cancelar</button>
                <button type="button" class="admin-confirm-save" id="admin-role-confirm-button">Sim, guardar</button>
            </div>
        </div>
    </div>

    <script>
        const userSearch = document.getElementById('admin-user-search');
        const roleFilter = document.getElementById('admin-role-filter');
        const groupSearch = document.getElementById('admin-group-search');
        const roleConfirm = document.getElementById('admin-role-confirm');
        const roleConfirmText = document.getElementById('admin-role-confirm-text');
        const roleConfirmButton = document.getElementById('admin-role-confirm-button');
        const roleCancelButton = document.getElementById('admin-role-cancel');
        const roleChangeText = @json('Alterar :name de :oldRole para :newRole?');
        let formWaitingForConfirmation = null;

        if (userSearch) {
            userSearch.addEventListener('input', function () {
                filterUsers();
            });
        }

        if (roleFilter) {
            roleFilter.addEventListener('change', function () {
                filterUsers();
            });
        }

        if (groupSearch) {
            groupSearch.addEventListener('input', function () {
                filterAdminRows('[data-admin-group-row]', this.value);
            });
        }

        document.querySelectorAll('.admin-role-select').forEach(function (select) {
            select.addEventListener('change', function () {
                this.className = 'admin-role-select role-' + this.value;
            });
        });

        document.querySelectorAll('[data-role-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const selectedRole = form.querySelector('select[name="role"]').value;

                if (form.dataset.confirmed === 'yes') {
                    return;
                }

                event.preventDefault();
                formWaitingForConfirmation = form;
                roleConfirmText.textContent = roleChangeText
                    .replace(':name', form.dataset.userName)
                    .replace(':oldRole', form.dataset.currentRole)
                    .replace(':newRole', selectedRole);
                roleConfirm.classList.add('open');
            });
        });

        roleCancelButton.addEventListener('click', function () {
            formWaitingForConfirmation = null;
            roleConfirm.classList.remove('open');
        });

        roleConfirmButton.addEventListener('click', function () {
            if (!formWaitingForConfirmation) {
                return;
            }

            formWaitingForConfirmation.dataset.confirmed = 'yes';
            formWaitingForConfirmation.submit();
        });

        function filterAdminRows(selector, value) {
            const search = value.toLowerCase().trim();

            document.querySelectorAll(selector).forEach(function (row) {
                const text = row.dataset.adminSearch || '';
                row.classList.toggle('admin-row-hidden', search && ! text.includes(search));
            });
        }

        function filterUsers() {
            const search = userSearch.value.toLowerCase().trim();
            const selectedRole = roleFilter.value;

            document.querySelectorAll('[data-admin-user-row]').forEach(function (row) {
                const text = row.dataset.adminSearch || '';
                const role = row.dataset.adminRole || '';
                const matchesText = !search || text.includes(search);
                const matchesRole = !selectedRole || role === selectedRole;

                row.classList.toggle('admin-row-hidden', !matchesText || !matchesRole);
            });
        }
    </script>
@endsection
