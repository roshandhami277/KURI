@extends('layouts.nav')

@section('title', 'Admin panel')

@section('content')
    <div class="page-heading">
        <p>Admin panel</p>
        <h1>Kuri controls.</h1>
        <span>Manage users and see the course groups that exist in Kuri.</span>
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
            <small>Total users</small>
            <strong>{{ $stats['users'] }}</strong>
        </div>
        <div>
            <small>Students</small>
            <strong>{{ $stats['students'] }}</strong>
        </div>
        <div>
            <small>Teachers</small>
            <strong>{{ $stats['teachers'] }}</strong>
        </div>
        <div>
            <small>Admins</small>
            <strong>{{ $stats['admins'] }}</strong>
        </div>
        <div>
            <small>Groups</small>
            <strong>{{ $stats['groups'] }}</strong>
        </div>
    </section>

    <section class="admin-layout">
        <div class="admin-panel-card">
            <div class="admin-section-heading">
                <div>
                    <p>Users</p>
                    <h2>All accounts</h2>
                </div>
                <span>{{ $users->count() }} users</span>
            </div>

            <div class="admin-user-tools">
                <input class="admin-search" id="admin-user-search" type="text" placeholder="Search users by name, email, role, course..." autocomplete="off">

                <select class="admin-filter-select" id="admin-role-filter">
                    <option value="">All roles</option>
                    <option value="student">Students</option>
                    <option value="teacher">Teachers</option>
                    <option value="admin">Admins</option>
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
                                <span class="admin-role-pill role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                            </div>
                            <small>{{ $user->email }}</small>
                            <span>
                                @if ($user->isAdmin())
                                    Admin account
                                @else
                                    {{ $user->course?->name ?? 'No course' }}
                                    ·
                                    {{ $user->schoolClass?->name ?? 'No class' }}
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
                                <span>Role</span>
                                <select class="admin-role-select role-{{ $user->role }}" name="role">
                                    <option value="student" @selected($user->role === 'student')>Student</option>
                                    <option value="teacher" @selected($user->role === 'teacher')>Teacher</option>
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                </select>
                            </label>

                            <button type="submit">Save</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="admin-panel-card">
            <div class="admin-section-heading">
                <div>
                    <p>Groups</p>
                    <h2>All groups</h2>
                </div>
                <span>{{ $groups->count() + $teacherGroups->count() }} groups</span>
            </div>

            <input class="admin-search" id="admin-group-search" type="text" placeholder="Search groups..." autocomplete="off">

            <div class="admin-group-list">
                <p>Course groups</p>
                @foreach ($groups as $group)
                    <div data-admin-group-row data-admin-search="{{ strtolower($group->name.' course group') }}">
                        <strong>{{ $group->name }}</strong>
                        <small>{{ $group->users_count + $adminCount }} members including admins</small>
                    </div>
                @endforeach

                <p>Teacher groups</p>
                @forelse ($teacherGroups as $group)
                    <div data-admin-group-row data-admin-search="{{ strtolower($group->name.' '.$group->teacher->name.' teacher group') }}">
                        <strong>{{ $group->name }}</strong>
                        <small>{{ $group->members_count + $adminCount + 1 }} members including admins · {{ $group->teacher->name }}</small>
                    </div>
                @empty
                    <small>No teacher groups yet.</small>
                @endforelse
            </div>
        </aside>
    </section>

    @foreach ($users as $user)
        <div id="user-info-{{ $user->id }}" class="admin-user-overlay">
            <div class="admin-user-box">
                <div class="admin-user-box-top">
                    <div>
                        <p>User information</p>
                        <h2>{{ $user->name }}</h2>
                    </div>
                    <a href="#">Close</a>
                </div>

                <div class="admin-info-grid">
                    <div>
                        <small>Email</small>
                        <strong>{{ $user->email }}</strong>
                    </div>

                    <div>
                        <small>Role</small>
                        <strong>{{ ucfirst($user->role) }}</strong>
                    </div>

                    <div>
                        <small>Course group</small>
                        <strong>{{ $user->isAdmin() ? 'Admin account' : ($user->course?->name ?? 'No course') }}</strong>
                    </div>

                    <div>
                        <small>Class</small>
                        <strong>{{ $user->isAdmin() ? 'Admin account' : ($user->schoolClass?->name ?? 'No class') }}</strong>
                    </div>

                    <div>
                        <small>Account created</small>
                        <strong>{{ $user->created_at->format('d M Y H:i') }}</strong>
                    </div>

                    <div>
                        <small>Last updated</small>
                        <strong>{{ $user->updated_at->format('d M Y H:i') }}</strong>
                    </div>
                </div>

                @if ($user->id !== auth()->id())
                    <form class="admin-delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account? This cannot be undone.');">
                        @csrf
                        @method('DELETE')

                        <button type="submit">Delete user</button>
                    </form>
                @else
                    <p class="admin-self-note">This is your account, so it cannot be deleted here.</p>
                @endif
            </div>
        </div>
    @endforeach

    <div class="admin-confirm-overlay" id="admin-role-confirm">
        <div class="admin-confirm-box">
            <p>Confirm role change</p>
            <h2>Are you sure?</h2>
            <span id="admin-role-confirm-text">This will change the user role.</span>

            <div>
                <button type="button" class="admin-confirm-cancel" id="admin-role-cancel">Cancel</button>
                <button type="button" class="admin-confirm-save" id="admin-role-confirm-button">Yes, save</button>
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
                roleConfirmText.textContent = 'Change ' + form.dataset.userName + ' from '
                    + form.dataset.currentRole + ' to ' + selectedRole + '?';
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
