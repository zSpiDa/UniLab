<!-- Aggiornamento delle informazioni del profilo con invio email di conferma-->
<section>
    <form method="POST" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('PATCH')
        <div class="mb-3">
            <label for="name" class="form-label"><b>Nome</b></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @error('name')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label"><b>Email</b></label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label"><b>Nuova Password (lascia vuoto per mantenere quella attuale)</b></label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Inserisci una nuova password se vuoi cambiarla">
            @error('password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
         <div class="mb-3">
            <label for="password_confirmation" class="form-label"><b>Conferma Nuova Password</b></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Conferma la nuova password">
        </div>
         <div class="mb-3">
            <label for="current_password" class="form-label"><b>Password Attuale</b></label>
            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Inserisci la tua password attuale per confermare le modifiche" required>
            @error('current_password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
    </form>
</section>
