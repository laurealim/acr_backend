@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit User')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('tyro-dashboard.users.index') }}">Users</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-description">Update user information and roles.</p>
        </div>
        <a href="{{ route('tyro-dashboard.users.index') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
    </div>
</div>

<div class="grid-2">
    <!-- User Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        <form action="{{ route('tyro-dashboard.users.update', $editUser->id) }}" method="POST">
            @csrf
            @method('PUT')
            @php($employee = $editUser->employees)
            <div class="card-body">
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name', $editUser->name) }}" required>
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email', $editUser->email) }}" required>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        New Password <span class="form-label-optional">(leave blank to keep current)</span>
                    </label>
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" placeholder="••••••••">
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Assign Roles</label>
                    <div class="checkbox-list">
                        @foreach($roles as $role)
                        <label class="checkbox-item">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="checkbox-input" {{ in_array($role->id, old('roles', $editUser->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <div class="checkbox-item-content">
                                <div class="checkbox-item-title">{{ $role->name }}</div>
                                <div class="checkbox-item-description">{{ $role->slug }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h3 class="card-title" style="margin-bottom: 1rem;">Employee Profile</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="employee_id" class="form-label">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" class="form-input @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $employee->employee_id ?? '') }}" required>
                            @error('employee_id')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name_bangla" class="form-label">Name (Bangla)</label>
                            <input type="text" id="name_bangla" name="name_bangla" class="form-input @error('name_bangla') is-invalid @enderror" value="{{ old('name_bangla', $employee->name_bangla ?? '') }}" required>
                            @error('name_bangla')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="office_id" class="form-label">Office</label>
                            <select id="office_id" name="office_id" class="form-select @error('office_id') is-invalid @enderror" required>
                                <option value="">Select Office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ old('office_id', $employee->office_id ?? '') == $office->id ? 'selected' : '' }}>{{ $office->name_bangla }} ({{ $office->name_english }})</option>
                                @endforeach
                            </select>
                            @error('office_id')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="designation" class="form-label">Designation</label>
                            <input type="text" id="designation" name="designation" class="form-input @error('designation') is-invalid @enderror" value="{{ old('designation', $employee->designation ?? '') }}" required>
                            @error('designation')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="grade" class="form-label">Grade</label>
                            <select id="grade" name="grade" class="form-select @error('grade') is-invalid @enderror" required>
                                <option value="">Select Grade</option>
                                @for($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}" {{ old('grade', $employee->grade ?? '') == $i ? 'selected' : '' }}>Grade {{ $i }}</option>
                                @endfor
                            </select>
                            @error('grade')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="employee_class" class="form-label">Employee Class</label>
                            <select id="employee_class" name="employee_class" class="form-select @error('employee_class') is-invalid @enderror">
                                <option value="">Auto (based on grade)</option>
                                <option value="1st_class" {{ old('employee_class', $employee->employee_class ?? '') == '1st_class' ? 'selected' : '' }}>1st Class</option>
                                <option value="2nd_class" {{ old('employee_class', $employee->employee_class ?? '') == '2nd_class' ? 'selected' : '' }}>2nd Class</option>
                                <option value="3rd_class" {{ old('employee_class', $employee->employee_class ?? '') == '3rd_class' ? 'selected' : '' }}>3rd Class</option>
                                <option value="4th_class" {{ old('employee_class', $employee->employee_class ?? '') == '4th_class' ? 'selected' : '' }}>4th Class</option>
                            </select>
                            @error('employee_class')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" required>
                            @error('date_of_birth')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nid_number" class="form-label">NID Number</label>
                            <input type="text" id="nid_number" name="nid_number" class="form-input @error('nid_number') is-invalid @enderror" value="{{ old('nid_number', $employee->nid_number ?? '') }}" placeholder="10/13/17 digits">
                            @error('nid_number')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="father_name" class="form-label">Father's Name</label>
                            <input type="text" id="father_name" name="father_name" class="form-input @error('father_name') is-invalid @enderror" value="{{ old('father_name', $employee->father_name ?? '') }}" required>
                            @error('father_name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="mother_name" class="form-label">Mother's Name</label>
                            <input type="text" id="mother_name" name="mother_name" class="form-input @error('mother_name') is-invalid @enderror" value="{{ old('mother_name', $employee->mother_name ?? '') }}" required>
                            @error('mother_name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                <option value="">Select</option>
                                <option value="male" {{ old('gender', $employee->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $employee->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $employee->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="marital_status" class="form-label">Marital Status</label>
                            <select id="marital_status" name="marital_status" class="form-select @error('marital_status') is-invalid @enderror" required>
                                <option value="">Select</option>
                                <option value="single" {{ old('marital_status', $employee->marital_status ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ old('marital_status', $employee->marital_status ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ old('marital_status', $employee->marital_status ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ old('marital_status', $employee->marital_status ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                            @error('marital_status')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="number_of_children" class="form-label">Number of Children</label>
                            <input type="number" id="number_of_children" name="number_of_children" class="form-input @error('number_of_children') is-invalid @enderror" value="{{ old('number_of_children', $employee->number_of_children ?? '') }}" min="0">
                            @error('number_of_children')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="blood_group" class="form-label">Blood Group</label>
                            <input type="text" id="blood_group" name="blood_group" class="form-input @error('blood_group') is-invalid @enderror" value="{{ old('blood_group', $employee->blood_group ?? '') }}" placeholder="e.g., A+">
                            @error('blood_group')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="personal_phone" class="form-label">Personal Phone</label>
                            <input type="text" id="personal_phone" name="personal_phone" class="form-input @error('personal_phone') is-invalid @enderror" value="{{ old('personal_phone', $employee->personal_phone ?? '') }}">
                            @error('personal_phone')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="highest_education" class="form-label">Highest Education</label>
                            <select id="highest_education" name="highest_education" class="form-select @error('highest_education') is-invalid @enderror" required>
                                <option value="">Select</option>
                                <option value="Primary" {{ old('highest_education', $employee->highest_education ?? '') == 'Primary' ? 'selected' : '' }}>Primary</option>
                                <option value="JSC" {{ old('highest_education', $employee->highest_education ?? '') == 'JSC' ? 'selected' : '' }}>JSC</option>
                                <option value="SSC" {{ old('highest_education', $employee->highest_education ?? '') == 'SSC' ? 'selected' : '' }}>SSC</option>
                                <option value="HSC" {{ old('highest_education', $employee->highest_education ?? '') == 'HSC' ? 'selected' : '' }}>HSC</option>
                                <option value="Diploma" {{ old('highest_education', $employee->highest_education ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                <option value="Bachelor" {{ old('highest_education', $employee->highest_education ?? '') == 'Bachelor' ? 'selected' : '' }}>Bachelor</option>
                                <option value="Masters" {{ old('highest_education', $employee->highest_education ?? '') == 'Masters' ? 'selected' : '' }}>Masters</option>
                                <option value="MPhil" {{ old('highest_education', $employee->highest_education ?? '') == 'MPhil' ? 'selected' : '' }}>MPhil</option>
                                <option value="PhD" {{ old('highest_education', $employee->highest_education ?? '') == 'PhD' ? 'selected' : '' }}>PhD</option>
                            </select>
                            @error('highest_education')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="govt_service_join_date" class="form-label">Govt. Service Join Date</label>
                            <input type="date" id="govt_service_join_date" name="govt_service_join_date" class="form-input @error('govt_service_join_date') is-invalid @enderror" value="{{ old('govt_service_join_date', $employee->govt_service_join_date ?? '') }}" required>
                            @error('govt_service_join_date')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="current_position_join_date" class="form-label">Current Position Join Date</label>
                            <input type="date" id="current_position_join_date" name="current_position_join_date" class="form-input @error('current_position_join_date') is-invalid @enderror" value="{{ old('current_position_join_date', $employee->current_position_join_date ?? '') }}" required>
                            @error('current_position_join_date')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gazetted_post_join_date" class="form-label">Gazetted Post Join Date</label>
                            <input type="date" id="gazetted_post_join_date" name="gazetted_post_join_date" class="form-input @error('gazetted_post_join_date') is-invalid @enderror" value="{{ old('gazetted_post_join_date', $employee->gazetted_post_join_date ?? '') }}">
                            @error('gazetted_post_join_date')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="cadre_join_date" class="form-label">Cadre Join Date</label>
                            <input type="date" id="cadre_join_date" name="cadre_join_date" class="form-input @error('cadre_join_date') is-invalid @enderror" value="{{ old('cadre_join_date', $employee->cadre_join_date ?? '') }}">
                            @error('cadre_join_date')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prl_date" class="form-label">PRL Date</label>
                            <input type="date" id="prl_date" name="prl_date" class="form-input @error('prl_date') is-invalid @enderror" value="{{ old('prl_date', $employee->prl_date ?? '') }}">
                            @error('prl_date')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="cadre" class="form-label">Cadre</label>
                            <input type="text" id="cadre" name="cadre" class="form-input @error('cadre') is-invalid @enderror" value="{{ old('cadre', $employee->cadre ?? '') }}">
                            @error('cadre')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="batch" class="form-label">Batch</label>
                            <input type="text" id="batch" name="batch" class="form-input @error('batch') is-invalid @enderror" value="{{ old('batch', $employee->batch ?? '') }}">
                            @error('batch')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="photo" class="form-label">Photo (Path)</label>
                            <input type="text" id="photo" name="photo" class="form-input @error('photo') is-invalid @enderror" value="{{ old('photo', $employee->photo ?? '') }}" placeholder="storage/photos/...">
                            @error('photo')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="is_dossier_keeper" class="form-label">Is Dossier Keeper</label>
                            <select id="is_dossier_keeper" name="is_dossier_keeper" class="form-select @error('is_dossier_keeper') is-invalid @enderror" required>
                                <option value="1" {{ old('is_dossier_keeper', (string)($employee->is_dossier_keeper ?? '0')) == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_dossier_keeper', (string)($employee->is_dossier_keeper ?? '0')) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_dossier_keeper')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="is_active" class="form-label">Is Active</label>
                            <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                                <option value="1" {{ old('is_active', (string)($employee->is_active ?? '1')) == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_active', (string)($employee->is_active ?? '1')) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_active')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="permanent_address" class="form-label">Permanent Address</label>
                            <textarea id="permanent_address" name="permanent_address" class="form-textarea @error('permanent_address') is-invalid @enderror" rows="3">{{ old('permanent_address', $employee->permanent_address ?? '') }}</textarea>
                            @error('permanent_address')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="present_address" class="form-label">Present Address</label>
                            <textarea id="present_address" name="present_address" class="form-textarea @error('present_address') is-invalid @enderror" rows="3">{{ old('present_address', $employee->present_address ?? '') }}</textarea>
                            @error('present_address')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="suspended_at" class="form-label">Suspended At (optional)</label>
                            <input type="date" id="suspended_at" name="suspended_at" class="form-input @error('suspended_at') is-invalid @enderror" value="{{ old('suspended_at', $employee->suspended_at ? $employee->suspended_at->format('Y-m-d') : '') }}">
                            @error('suspended_at')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="suspension_reason" class="form-label">Suspension Reason (optional)</label>
                            <textarea id="suspension_reason" name="suspension_reason" class="form-textarea @error('suspension_reason') is-invalid @enderror" rows="3">{{ old('suspension_reason', $employee->suspension_reason ?? '') }}</textarea>
                            @error('suspension_reason')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('tyro-dashboard.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Account Status -->
    <div>
        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-header">
                <h3 class="card-title">Account Status</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div class="user-cell-avatar" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        {{ strtoupper(substr($editUser->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 600; color: var(--foreground);">{{ $editUser->name }}</div>
                        <div style="font-size: 0.875rem; color: var(--muted-foreground);">Member since {{ $editUser->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <div style="padding: 1rem; background-color: var(--muted); border-radius: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.875rem; color: var(--muted-foreground);">Status</span>
                        @if(method_exists($editUser, 'isSuspended') && $editUser->isSuspended())
                            <span class="badge badge-danger">Suspended</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </div>
                    @if(method_exists($editUser, 'isSuspended') && $editUser->isSuspended() && method_exists($editUser, 'getSuspensionReason') && $editUser->getSuspensionReason())
                        <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border);">
                            <span style="font-size: 0.75rem; color: var(--muted-foreground);">Suspension Reason:</span>
                            <p style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">{{ $editUser->getSuspensionReason() }}</p>
                        </div>
                    @endif
                </div>

                <!-- 2FA Status & Reset -->
                @if(config('tyro-login.two_factor.enabled'))
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.875rem; color: var(--muted-foreground);">Two-Factor Authentication</span>
                        @if($editUser->two_factor_secret)
                            <span class="badge badge-success">Enabled</span>
                        @else
                            <span class="badge badge-secondary">Disabled</span>
                        @endif
                    </div>
                    @if($editUser->two_factor_secret)
                        <form action="{{ route('tyro-dashboard.users.2fa.reset', $editUser->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-warning" style="width: 100%;" onclick="return confirm('Are you sure you want to reset 2FA for this user?')">
                                Reset 2FA
                            </button>
                        </form>
                    @endif
                </div>
                @endif

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    @if(method_exists($editUser, 'isSuspended') && $editUser->isSuspended())
                        <form action="{{ route('tyro-dashboard.users.unsuspend', $editUser->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" style="width: 100%;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Unsuspend User
                            </button>
                        </form>
                    @elseif($editUser->id !== $user->id)
                        <button type="button" class="btn btn-warning" style="width: 100%;" onclick="openSuspendModal()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Suspend User
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($editUser->id !== $user->id)
        <div class="card" style="border-color: var(--destructive);">
            <div class="card-header" style="background-color: color-mix(in srgb, var(--destructive), transparent 90%);">
                <h3 class="card-title" style="color: var(--destructive);">Danger Zone</h3>
            </div>
            <div class="card-body">
                <p style="font-size: 0.875rem; color: var(--muted-foreground); margin-bottom: 1rem;">
                    Once you delete a user, there is no going back. Please be certain.
                </p>
                <form action="{{ route('tyro-dashboard.users.destroy', $editUser->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width: 100%;" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete User
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal-overlay" id="suspendModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Suspend User</h3>
            <button type="button" class="modal-close" onclick="closeModal('suspendModal')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="{{ route('tyro-dashboard.users.suspend', $editUser->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="margin-bottom: 1rem; color: var(--muted-foreground);">
                    You are about to suspend <strong>{{ $editUser->name }}</strong>. This will revoke all their active sessions.
                </p>
                <div class="form-group">
                    <label for="reason" class="form-label">
                        Reason for suspension <span class="form-label-optional">(optional)</span>
                    </label>
                    <textarea id="reason" name="reason" class="form-textarea" rows="3" placeholder="Enter a reason for suspension..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('suspendModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Suspend User</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openSuspendModal() {
        openModal('suspendModal');
    }
</script>
@endpush
@endsection
