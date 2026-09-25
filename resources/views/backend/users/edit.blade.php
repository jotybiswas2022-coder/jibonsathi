@extends('backend.layouts.app')

@section('title', 'Edit '.$user->name)
@section('crumb', 'Members · Edit member')

@section('content')
    <div class="card card-pad" style="max-width:640px">
        <form method="POST" action="{{ route('backend.users.update', $user) }}"
              data-confirm-title="Save changes to {{ $user->name }}?"
              data-confirm="The updated details replace the current profile straight away."
              data-confirm-ok="Save changes" data-confirm-icon="question"
              data-confirm-color="#8B1E3F" data-confirm-focus-cancel>
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>
                        Please fix the errors below:
                        <ul style="margin:6px 0 0;padding-left:18px">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                    </div>
                </div>
            @endif

            <div class="field-group mb-4">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="input" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="input">
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $user->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="input" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="input" value="{{ old('phone', $user->phone) }}" required>
                </div>
            </div>

            <div class="field">
                <label for="admin_note">Admin note (sent to the member when status changes)</label>
                <textarea name="admin_note" id="admin_note" class="input" rows="3" maxlength="500"
                          placeholder="Optional reason, e.g. policy reminder…">{{ old('admin_note') }}</textarea>
            </div>

            <div class="flex gap-2" style="justify-content:space-between;margin-top:6px">
                <a href="{{ route('backend.users.show', $user) }}" class="btn btn-outline">Cancel</a>
                <button class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
@endsection