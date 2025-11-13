<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <script>
            function preventBack(){
                window.history.forward();
            }
            
            setTimeout("preventBack()", 0);
            window.onunload=function(){null;}
        </script>

    <style>
        .login-input {
            width: 250px !important;
            margin: 0 auto;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>

    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf
 
        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Username')" class="text-center" />
            <x-text-input id="username" class="block mt-1 login-input" type="text" name="username" :value="old('username')" required autofocus />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-center" />

            <x-text-input id="password" class="block mt-1 login-input"
                            type="password"
                            name="password"
                            required/>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-center mt-4">

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
