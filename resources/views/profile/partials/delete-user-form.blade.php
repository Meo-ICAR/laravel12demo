<section>
    <div class="card-header">
        <h3 class="card-title">{{ __('Delete Account') }}</h3>
        <div class="card-tools">
            <p class="text-sm text-muted">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
            </p>
        </div>
    </div>

    <form method="post" action="{{ route('profile.destroy') }}" class="mt-6">
        @csrf
        @method('delete')

        <div class="form-group">
            <label for="password">{{ __('Password') }}</label>
            <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password" name="password" autocomplete="current-password">
            @error('password', 'userDeletion')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete your account?') }}')">
                {{ __('Delete Account') }}
            </button>
        </div>
    </form>
</section>
