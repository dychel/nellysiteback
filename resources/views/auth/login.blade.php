<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Nelly Commerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            border: 1px solid #f0f0f0;
            position: relative;
        }
        
        .login-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1e293b;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        
        .admin-icon i {
            font-size: 28px;
            color: white;
        }
        
        .login-header h1 {
            font-weight: 700;
            font-size: 28px;
            margin: 0 0 8px;
            color: #1e293b;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.7;
            font-size: 16px;
            font-weight: 500;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #374151;
            font-size: 15px;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 18px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px 14px 50px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #f9fafb;
            color: #374151;
        }
        
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
            background-color: white;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 14px;
            margin-top: 6px;
            padding-left: 5px;
        }
        
        .status-message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 14px;
            display: none;
        }
        
        .status-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
        }
        
        .checkbox-custom {
            width: 20px;
            height: 20px;
            border-radius: 5px;
            border: 1px solid #d1d5db;
            position: relative;
            cursor: pointer;
            background-color: white;
            transition: all 0.2s;
        }
        
        .checkbox-custom.checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        .checkbox-custom.checked::after {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 13px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .remember-text {
            font-size: 15px;
            color: #4b5563;
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }
        
        .forgot-password {
            font-size: 14px;
            color: #3b82f6;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(59, 130, 246, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .security-note {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #6b7280;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 20px;
                align-items: stretch;
            }
            
            .forgot-password {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="admin-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Connexion à l'espace Admin</h1>
            <p>Nelly Commerce - Gestion du site</p>
        </div>
        
        <div class="login-body">
            <!-- Session Status -->
            <div class="status-message status-success" id="status-message">
                <!-- Le statut de session sera affiché ici dynamiquement -->
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" class="form-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="votre@email.com">
                    </div>
                    <div class="error-message" id="email-error">
                        <!-- Les erreurs email seront affichées ici dynamiquement -->
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key input-icon"></i>
                        <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password" placeholder="Votre mot de passe">
                    </div>
                    <div class="error-message" id="password-error">
                        <!-- Les erreurs password seront affichées ici dynamiquement -->
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="remember-me">
                    <div class="checkbox-custom" id="remember-checkbox"></div>
                    <input id="remember_me" type="checkbox" class="hidden" name="remember">
                    <span class="remember-text">Se souvenir de moi</span>
                </div>

                <div class="form-footer">
                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">
                            Mot de passe oublié ?
                        </a>
                    @endif

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </div>
            </form>
            
            <div class="security-note">
                <i class="fas fa-shield-alt"></i> Accès sécurisé - Réservé aux administrateurs
            </div>
        </div>
    </div>

    <script>
        // Gestion de la case à cocher personnalisée
        document.getElementById('remember-checkbox').addEventListener('click', function() {
            const checkbox = document.getElementById('remember_me');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('checked', checkbox.checked);
        });
        
        // Simulation d'un message de statut (à supprimer en production)
        setTimeout(() => {
            const statusMessage = document.getElementById('status-message');
            statusMessage.textContent = "Veuillez vous connecter pour accéder au panneau d'administration";
            statusMessage.style.display = 'block';
        }, 500);
        
        // Simulation d'erreurs (à supprimer en production)
        setTimeout(() => {
            // Simuler une erreur d'email
            const emailError = document.getElementById('email-error');
            emailError.textContent = "L'adresse email fournie n'est pas valide";
            emailError.style.display = 'block';
            
            // Simuler une erreur de mot de passe
            const passwordError = document.getElementById('password-error');
            passwordError.textContent = "Le mot de passe est incorrect";
            passwordError.style.display = 'block';
        }, 3000);
    </script>
</body>
</html>